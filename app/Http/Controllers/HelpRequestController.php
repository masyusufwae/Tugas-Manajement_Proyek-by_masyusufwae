<?php

namespace App\Http\Controllers;

use App\Models\HelpRequest;
use App\Models\Subtask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'team_lead') {
            $helpRequests = HelpRequest::with(['subtask.card', 'requester'])
                ->where('team_lead_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $helpRequests = HelpRequest::with(['subtask.card', 'teamLead'])
                ->where('requester_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('help-requests.index', compact('helpRequests'));
    }

    public function create(Subtask $subtask)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['developer', 'designer'])) {
            abort(403, 'Only developers and designers can request help');
        }

        // Get team lead for this subtask's project
        $teamLead = User::where('role', 'team_lead')
            ->whereHas('projectMembers', function($query) use ($subtask) {
                $query->where('project_id', $subtask->card->board->project_id);
            })
            ->first();

        if (!$teamLead) {
            return back()->with('error', 'No team lead found for this project');
        }

        return view('help-requests.create', compact('subtask', 'teamLead'));
    }

    public function store(Request $request, Subtask $subtask)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['developer', 'designer'])) {
            abort(403, 'Only developers and designers can request help');
        }

        $request->validate([
            'team_lead_id' => 'required|exists:users,user_id'
        ]);

        // Check if there's already a pending request for this subtask
        $existingRequest = HelpRequest::where('subtask_id', $subtask->subtask_id)
            ->where('requester_id', $user->user_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending help request for this subtask');
        }

        HelpRequest::create([
            'subtask_id' => $subtask->subtask_id,
            'requester_id' => $user->user_id,
            'team_lead_id' => $request->team_lead_id,
            'message' => 'Help request for subtask: ' . $subtask->subtask_title,
            'status' => 'pending'
        ]);

        return redirect()->route('help-requests.index')
            ->with('success', 'Help request sent successfully');
    }

    public function show(HelpRequest $helpRequest)
    {
        $user = Auth::user();
        
        // Check if user has access to this help request
        if ($user->user_id !== $helpRequest->requester_id && $user->user_id !== $helpRequest->team_lead_id) {
            abort(403, 'You do not have access to this help request');
        }

        $helpRequest->load(['subtask.card', 'requester', 'teamLead']);

        return view('help-requests.show', compact('helpRequest'));
    }

    public function respond(Request $request, HelpRequest $helpRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'team_lead' || $user->user_id !== $helpRequest->team_lead_id) {
            abort(403, 'Only the assigned team lead can respond to this request');
        }

        $request->validate([
            'response' => 'nullable|string|max:1000',
            'status' => 'required|in:responded,resolved,rejected'
        ]);

        $helpRequest->update([
            'response' => $request->response,
            'status' => $request->status
        ]);

        return back()->with('success', 'Response sent successfully');
    }

    public function markResolved(HelpRequest $helpRequest)
    {
        $user = Auth::user();
        
        if ($user->user_id !== $helpRequest->requester_id) {
            abort(403, 'Only the requester can mark this as resolved');
        }

        $helpRequest->update(['status' => 'resolved']);

        return back()->with('success', 'Help request marked as resolved');
    }
}
