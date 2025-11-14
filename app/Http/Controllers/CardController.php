<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Board;
use App\Models\CardAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CardController extends Controller
{
    /**
     * Daftar cards dalam board
     */
    public function index(Board $board)
    {
        if (Auth::user()->role !== 'team_lead') {
            abort(403, 'Hanya Team Lead yang bisa melihat daftar card');
        }

        $cards = Card::with([
                'assignments.user',
                'subtasks.comments',
                'comments'
            ])
            ->where('board_id', $board->board_id)
            ->get();

        return view('team_lead.cards.index', compact('cards', 'board'));
    }

    /**
     * Form tambah card
     */



    public function create(Board $board)
{
    // Ambil users dengan role developer atau designer yang merupakan member project
    $availableUsers = User::whereIn('role', ['developer', 'designer'])
        ->whereHas('projectMembers', function($query) use ($board) {
            $query->where('project_id', $board->project_id);
        })
        ->get();

    return view('team_lead.cards.create', compact('board', 'availableUsers'));
}
    /**
     * Simpan card baru + assign user (hanya 1)
     */
   public function store(Request $request, Board $board)
{
    $request->validate([
        'card_title' => 'required|string|max:100',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'estimated_hours' => 'nullable|numeric|min:0',
        'due_date' => 'nullable|date',
        'user_ids' => 'required|array',
        'user_ids.*' => 'exists:users,user_id'
    ]);

    // Buat card
    $card = Card::create([
        'board_id' => $board->board_id,
        'card_title' => $request->card_title,
        'description' => $request->description,
        'priority' => $request->priority,
        'estimated_hours' => $request->estimated_hours,
        'due_date' => $request->due_date,
        'created_by' => auth()->id(),
    ]);

    // Assign users ke card
    foreach ($request->user_ids as $userId) {
        CardAssignment::create([
            'card_id' => $card->card_id,
            'user_id' => $userId,
        ]);
    }

    return redirect()->route('team_lead.cards.index', $board)
        ->with('success', 'Card berhasil dibuat dan diassign ke anggota tim');
}

    /**
     * Form edit card
     */
    public function edit(Board $board, Card $card)
    {
        if (Auth::user()->role !== 'team_lead') abort(403);

        $card->load('assignments.user');
        return view('team_lead.cards.edit', compact('board', 'card'));
    }

    /**
     * Update card (hanya 1 user)
     */
    public function update(Request $request, Board $board, Card $card)
    {
        if (Auth::user()->role !== 'team_lead') abort(403);

        $request->validate([
            'card_title'      => 'required|string|max:255',
            'description'     => 'nullable|string',
            'priority'        => 'required|in:low,medium,high',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date'        => 'nullable|date|after_or_equal:today',
            'username'        => 'required|string'
        ]);

        $card->update([
            'card_title'      => $request->card_title,
            'description'     => $request->description,
            'priority'        => $request->priority,
            'estimated_hours' => $request->estimated_hours,
            'due_date'        => $request->due_date,
        ]);

        // Hapus assignment lama
        CardAssignment::where('card_id', $card->card_id)->delete();

        // Tambah assignment baru
        $user = User::where('username', $request->username)
            ->whereIn('role', ['developer', 'designer'])
            ->first();

        if ($user) {
            $hasActiveCard = CardAssignment::where('user_id', $user->user_id)
                ->where('card_id', '!=', $card->card_id)
                ->whereHas('card', function ($q) {
                    $q->whereIn('status', ['todo', 'in_progress', 'review']);
                })
                ->exists();

            if ($hasActiveCard) {
                throw ValidationException::withMessages([
                    'username' => "❌ User {$user->username} sedang ada tugas lain. Selesaikan dulu sebelum ambil card baru.",
                ]);
            }

            CardAssignment::create([
                'card_id'          => $card->card_id,
                'user_id'          => $user->user_id,
                'assignment_status'=> 'assigned',
                'assigned_at'      => now()
            ]);
        }

        return redirect()->route('team_lead.cards.index', $board)
            ->with('success', '✅ Card berhasil diperbarui!');
    }

    /**
     * Hapus card
     */
    public function destroy(Board $board, Card $card)
    {
        if (Auth::user()->role !== 'team_lead') abort(403);

        $card->delete();
        return redirect()->route('team_lead.cards.index', $board)
            ->with('success', '🗑️ Card berhasil dihapus!');
    }
}
