<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Project;

class MonitoringController extends Controller
{
    /**
     * Display monitoring dashboard
     */
    public function index()
    {
        return view('admin.monitoring.index');
    }

    /**
     * Get all projects from database with details and progress
     */
    public function getProjects()
    {
        $projects = Project::with(['boards.cards', 'members.user'])
            ->get()
            ->map(function ($project) {
                // Get all cards in this project
                $cards = collect();
                foreach ($project->boards as $board) {
                    $cards = $cards->merge($board->cards);
                }

                $totalCards = $cards->count();
                $doneCards = $cards->where('status', 'done')->count();
                $inProgressCards = $cards->where('status', 'in_progress')->count();
                $todoCards = $cards->where('status', 'todo')->count();

                // Calculate progress based on completed cards
                $progress = $totalCards > 0 ? round(($doneCards / $totalCards) * 100, 2) : 0;

                // Calculate total estimated and actual hours
                $estimatedHours = $cards->sum('estimated_hours') ?? 0;
                $actualHours = $cards->sum('actual_hours') ?? 0;

                // Determine project status based on cards
                $status = 'todo';
                if ($doneCards > 0 && $doneCards == $totalCards) {
                    $status = 'done';
                } elseif ($inProgressCards > 0 || $doneCards > 0) {
                    $status = 'in-progress';
                }

                // Check if project is overdue
                $isOverdue = false;
                if ($project->deadline && $status !== 'done') {
                    $isOverdue = strtotime($project->deadline) < strtotime('today');
                }

                // Get project members
                $members = $project->members->map(function ($member) {
                    return [
                        'id' => $member->user->user_id,
                        'name' => $member->user->full_name ?? $member->user->username,
                        'role' => $member->role
                    ];
                })->unique('id')->values();

                return [
                    'id' => $project->project_id,
                    'title' => $project->project_name,
                    'description' => $project->description,
                    'status' => $status,
                    'deadline' => $project->deadline ? date('Y-m-d', strtotime($project->deadline)) : null,
                    'created_at' => $project->created_at ? date('Y-m-d', strtotime($project->created_at)) : null,
                    'estimated_hours' => round($estimatedHours, 2),
                    'actual_hours' => round($actualHours, 2),
                    'progress' => $progress,
                    'total_cards' => $totalCards,
                    'done_cards' => $doneCards,
                    'in_progress_cards' => $inProgressCards,
                    'todo_cards' => $todoCards,
                    'total_boards' => $project->boards->count(),
                    'total_members' => $members->count(),
                    'members' => $members,
                    'is_overdue' => $isOverdue,
                ];
            });

        return response()->json($projects);
    }

    /**
     * Get monitoring statistics
     */
    public function getStats()
    {
        $projects = Project::with(['boards.cards'])->get();

        $totalProjects = $projects->count();
        $completedProjects = 0;
        $activeProjects = 0;
        $overdueProjects = 0;

        $totalProgressSum = 0;

        foreach ($projects as $project) {
            // Get all cards in this project
            $cards = collect();
            foreach ($project->boards as $board) {
                $cards = $cards->merge($board->cards);
            }

            $totalCards = $cards->count();
            $doneCards = $cards->where('status', 'done')->count();
            $inProgressCards = $cards->where('status', 'in_progress')->count();

            // Calculate progress
            $progress = $totalCards > 0 ? ($doneCards / $totalCards) * 100 : 0;
            $totalProgressSum += $progress;

            // Determine status
            if ($doneCards > 0 && $doneCards == $totalCards && $totalCards > 0) {
                $completedProjects++;
            } elseif ($inProgressCards > 0 || $doneCards > 0) {
                $activeProjects++;
            }

            // Check if overdue
            if ($project->deadline) {
                $isOverdue = strtotime($project->deadline) < strtotime('today');
                if ($isOverdue && !($doneCards > 0 && $doneCards == $totalCards && $totalCards > 0)) {
                    $overdueProjects++;
                }
            }
        }

        // Calculate average progress
        $progressOverview = $totalProjects > 0 ? round($totalProgressSum / $totalProjects, 2) : 0;

        // Calculate working members (users with active assignments)
        $workingMembers = DB::table('card_assignments')
            ->join('cards', 'card_assignments.card_id', '=', 'cards.card_id')
            ->where('cards.status', 'in_progress')
            ->distinct()
            ->count('card_assignments.user_id');

        // Calculate idle members (all users minus working members)
        $totalMembers = DB::table('project_members')
            ->distinct()
            ->count('user_id');
        $idleMembers = max(0, $totalMembers - $workingMembers);

        return response()->json([
            'projects' => [
                'total' => $totalProjects,
                'active' => $activeProjects,
                'completed' => $completedProjects,
                'overdue' => $overdueProjects,
            ],
            'members' => [
                'working' => $workingMembers,
                'idle' => $idleMembers,
            ],
            'progress_overview' => $progressOverview
        ]);
    }
}