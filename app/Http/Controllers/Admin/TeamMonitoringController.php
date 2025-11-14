<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamMonitoringController extends Controller
{
    public function index()
    {
        return view('admin.monitoring.team');
    }

    public function getTeamData()
    {
        $teamData = [
            'team_lead' => $this->getTeamLeadData(),
            'developers' => $this->getDevelopersData(),
            'designers' => $this->getDesignersData(),
            'overall_metrics' => $this->getOverallMetrics(),
            'last_updated' => now()->format('Y-m-d H:i:s')
        ];

        return response()->json($teamData);
    }

    public function updateTask(Request $request)
    {
        $request->validate([
            'member_id' => 'required',
            'task_id' => 'required',
            'status' => 'required|in:in_progress,done'
        ]);

        try {
            // Update card status
            DB::table('cards')
                ->where('card_id', $request->task_id)
                ->where('created_by', $request->member_id)
                ->update([
                    'status' => $request->status,
                    'actual_hours' => $request->status === 'done' ? 
                        DB::raw('COALESCE(estimated_hours, 0)') : 
                        DB::raw('COALESCE(actual_hours, 0)'),
                    'updated_at' => now()
                ]);

            $user = DB::table('users')->where('user_id', $request->member_id)->first();
            $task = DB::table('cards')->where('card_id', $request->task_id)->first();

            return response()->json([
                'success' => true,
                'message' => "Task '{$task->card_title}' untuk {$user->full_name} diperbarui ke status: " . 
                            ucfirst(str_replace('_', ' ', $request->status)),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui task: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getTeamLeadData()
    {
        // Ambil data team_lead dari tabel users
        $teamLead = DB::table('users')
            ->where('role', 'team_lead')
            ->first();

        if (!$teamLead) {
            return $this->getDefaultTeamLeadData();
        }

        // Ambil cards untuk team_lead
        $cards = DB::table('cards')
            ->where('created_by', $teamLead->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $cardStats = DB::table('cards')
            ->where('created_by', $teamLead->user_id)
            ->selectRaw('COUNT(*) as total_cards, 
                         SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed_cards,
                         SUM(COALESCE(estimated_hours, 0)) as total_estimated_hours,
                         SUM(COALESCE(actual_hours, 0)) as total_actual_hours')
            ->first();

        $projectsManaged = DB::table('cards')
            ->where('created_by', $teamLead->user_id)
            ->distinct('board_id')
            ->count('board_id');

        $teamSize = DB::table('users')
            ->whereIn('role', ['developer', 'designer'])
            ->count();

        return [
            'id' => $teamLead->user_id,
            'name' => $teamLead->full_name,
            'position' => 'Team Lead',
            'avatar' => $this->getInitials($teamLead->full_name),
            'performance' => $this->calculateUserPerformance($teamLead->user_id),
            'tasks_completed' => $cardStats->completed_cards ?? 0,
            'total_tasks' => $cardStats->total_cards ?? 0,
            'projects_managed' => $projectsManaged,
            'team_size' => $teamSize,
            'current_project' => $this->getCurrentProject($teamLead->user_id),
            'status' => $teamLead->current_task_status === 'working' ? 'busy' : 'available',
            'workload' => $this->calculateUserWorkload($teamLead->user_id),
            'last_active' => $this->getLastActive($teamLead->user_id),
            'tasks' => $cards->map(function($card) {
                return [
                    'id' => $card->card_id,
                    'name' => $card->card_title,
                    'status' => $card->status,
                    'progress' => $this->getProgressFromStatus($card->status)
                ];
            })->toArray()
        ];
    }

    private function getDevelopersData()
    {
        // Ambil data developers dari tabel users
        $developers = DB::table('users')
            ->where('role', 'developer')
            ->get();

        if ($developers->isEmpty()) {
            return [$this->getDefaultDeveloperData()];
        }

        return $developers->map(function($developer) {
            // Ambil cards untuk developer
            $cards = DB::table('cards')
                ->where('created_by', $developer->user_id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $cardStats = DB::table('cards')
                ->where('created_by', $developer->user_id)
                ->selectRaw('COUNT(*) as total_cards, 
                             SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed_cards,
                             SUM(COALESCE(estimated_hours, 0)) as total_estimated_hours,
                             SUM(COALESCE(actual_hours, 0)) as total_actual_hours')
                ->first();

            // Hitung subtasks untuk metrics commits
            $subtasksCount = DB::table('subtasks')
                ->join('cards', 'subtasks.card_id', '=', 'cards.card_id')
                ->where('cards.created_by', $developer->user_id)
                ->count();

            return [
                'id' => $developer->user_id,
                'name' => $developer->full_name,
                'position' => 'Developer',
                'avatar' => $this->getInitials($developer->full_name),
                'performance' => $this->calculateUserPerformance($developer->user_id),
                'tasks_completed' => $cardStats->completed_cards ?? 0,
                'total_tasks' => $cardStats->total_cards ?? 0,
                'current_project' => $this->getCurrentProject($developer->user_id),
                'specialization' => 'Developer',
                'status' => $developer->current_task_status === 'working' ? 'busy' : 'available',
                'workload' => $this->calculateUserWorkload($developer->user_id),
                'commits' => $subtasksCount, // Using subtasks count as commits metric
                'code_quality' => $this->calculateCodeQuality($developer->user_id),
                'tasks' => $cards->map(function($card) {
                    return [
                        'id' => $card->card_id,
                        'name' => $card->card_title,
                        'status' => $card->status,
                        'progress' => $this->getProgressFromStatus($card->status)
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    private function getDesignersData()
    {
        // Ambil data designers dari tabel users
        $designers = DB::table('users')
            ->where('role', 'designer')
            ->get();

        if ($designers->isEmpty()) {
            return [$this->getDefaultDesignerData()];
        }

        return $designers->map(function($designer) {
            // Ambil cards untuk designer
            $cards = DB::table('cards')
                ->where('created_by', $designer->user_id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $cardStats = DB::table('cards')
                ->where('created_by', $designer->user_id)
                ->selectRaw('COUNT(*) as total_cards, 
                             SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed_cards,
                             SUM(COALESCE(estimated_hours, 0)) as total_estimated_hours,
                             SUM(COALESCE(actual_hours, 0)) as total_actual_hours')
                ->first();

            // Hitung design-specific metrics
            $designsCreated = $cardStats->completed_cards ?? 0;
            $clientApproval = $this->calculateClientApproval($designer->user_id);

            return [
                'id' => $designer->user_id,
                'name' => $designer->full_name,
                'position' => 'Designer',
                'avatar' => $this->getInitials($designer->full_name),
                'performance' => $this->calculateUserPerformance($designer->user_id),
                'tasks_completed' => $cardStats->completed_cards ?? 0,
                'total_tasks' => $cardStats->total_cards ?? 0,
                'current_project' => $this->getCurrentProject($designer->user_id),
                'specialization' => 'Designer',
                'status' => $designer->current_task_status === 'working' ? 'busy' : 'available',
                'workload' => $this->calculateUserWorkload($designer->user_id),
                'designs_created' => $designsCreated,
                'client_approval' => $clientApproval,
                'tasks' => $cards->map(function($card) {
                    return [
                        'id' => $card->card_id,
                        'name' => $card->card_title,
                        'status' => $card->status,
                        'progress' => $this->getProgressFromStatus($card->status)
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    private function getOverallMetrics()
    {
        // Hitung total team members
        $totalTeamMembers = DB::table('users')
            ->whereIn('role', ['team_lead', 'developer', 'designer'])
            ->count();

        // Hitung total projects dari boards (jika ada tabel boards)
        $totalProjects = DB::table('cards')
            ->distinct('board_id')
            ->count('board_id');

        // Hitung cards completed
        $totalTasksCompleted = DB::table('cards')
            ->where('status', 'done')
            ->count();

        // Hitung active projects (boards dengan cards in_progress)
        $activeProjects = DB::table('cards')
            ->where('status', 'in_progress')
            ->distinct('board_id')
            ->count('board_id');

        // Hitung pending tasks
        $pendingTasks = DB::table('cards')
            ->where('status', 'todo')
            ->count();

        // Hitung team availability
        $teamAvailability = DB::table('users')
            ->whereIn('role', ['team_lead', 'developer', 'designer'])
            ->where('current_task_status', 'idle')
            ->count();

        // Hitung overall performance
        $teamMembers = DB::table('users')
            ->whereIn('role', ['team_lead', 'developer', 'designer'])
            ->get();

        $totalPerformance = 0;
        $count = 0;

        foreach ($teamMembers as $member) {
            $performance = $this->calculateUserPerformance($member->user_id);
            $totalPerformance += $performance;
            $count++;
        }

        $overallPerformance = $count > 0 ? round($totalPerformance / $count) : 0;

        return [
            'total_team_members' => $totalTeamMembers,
            'total_projects' => $totalProjects,
            'total_tasks_completed' => $totalTasksCompleted,
            'overall_performance' => $overallPerformance,
            'active_projects' => $activeProjects,
            'pending_tasks' => $pendingTasks,
            'team_availability' => $totalTeamMembers > 0 ? round(($teamAvailability / $totalTeamMembers) * 100) : 0
        ];
    }

    // Helper methods
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    private function calculateUserPerformance($userId)
    {
        $cardStats = DB::table('cards')
            ->where('created_by', $userId)
            ->selectRaw('COUNT(*) as total_cards, 
                         SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed_cards')
            ->first();

        if ($cardStats->total_cards > 0) {
            $completionRate = ($cardStats->completed_cards / $cardStats->total_cards) * 100;
            
            // Consider hours efficiency
            $hoursStats = DB::table('cards')
                ->where('created_by', $userId)
                ->where('status', 'done')
                ->selectRaw('SUM(COALESCE(estimated_hours, 0)) as total_estimated,
                             SUM(COALESCE(actual_hours, 0)) as total_actual')
                ->first();

            $efficiency = 100;
            if ($hoursStats->total_estimated > 0 && $hoursStats->total_actual > 0) {
                $efficiency = max(50, min(100, ($hoursStats->total_estimated / $hoursStats->total_actual) * 100));
            }

            return round(($completionRate * 0.7) + ($efficiency * 0.3));
        }

        return 0;
    }

    private function calculateUserWorkload($userId)
    {
        $currentWorkload = DB::table('cards')
            ->where('created_by', $userId)
            ->where('status', 'in_progress')
            ->count();

        // Convert to percentage (assuming 5 concurrent tasks is 100% workload)
        return min($currentWorkload * 20, 100);
    }

    private function getCurrentProject($userId)
    {
        $currentProject = DB::table('cards')
            ->where('created_by', $userId)
            ->where('status', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->value('card_title');

        return $currentProject ?? 'No Active Task';
    }

    private function getLastActive($userId)
    {
        $lastActivity = DB::table('cards')
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        if ($lastActivity) {
            return \Carbon\Carbon::parse($lastActivity)->diffForHumans();
        }

        return 'Never';
    }

    private function getProgressFromStatus($status)
    {
        switch ($status) {
            case 'done':
                return 100;
            case 'in_progress':
                return 50;
            case 'todo':
            default:
                return 0;
        }
    }

    private function calculateCodeQuality($userId)
    {
        // Calculate code quality based on completed tasks and efficiency
        $completedTasks = DB::table('cards')
            ->where('created_by', $userId)
            ->where('status', 'done')
            ->count();

        $efficiencyStats = DB::table('cards')
            ->where('created_by', $userId)
            ->where('status', 'done')
            ->selectRaw('SUM(COALESCE(estimated_hours, 0)) as total_estimated,
                         SUM(COALESCE(actual_hours, 0)) as total_actual')
            ->first();

        $baseQuality = min(100, $completedTasks * 5 + 70); // Base quality based on completed tasks
        
        // Adjust based on efficiency
        if ($efficiencyStats->total_estimated > 0 && $efficiencyStats->total_actual > 0) {
            $efficiencyRatio = $efficiencyStats->total_estimated / $efficiencyStats->total_actual;
            if ($efficiencyRatio >= 1) {
                $baseQuality += 10; // Completed faster than estimated
            } elseif ($efficiencyRatio >= 0.8) {
                $baseQuality += 5; // Close to estimate
            }
        }

        return min(100, $baseQuality);
    }

    private function calculateClientApproval($userId)
    {
        // Calculate client approval based on completed tasks without rejections
        $completedTasks = DB::table('cards')
            ->where('created_by', $userId)
            ->where('status', 'done')
            ->count();

        $rejectedTasks = DB::table('subtasks')
            ->join('cards', 'subtasks.card_id', '=', 'cards.card_id')
            ->where('cards.created_by', $userId)
            ->whereNotNull('subtasks.reject_reason')
            ->count();

        if ($completedTasks > 0) {
            $approvalRate = (($completedTasks - $rejectedTasks) / $completedTasks) * 100;
            return max(70, min(100, $approvalRate)); // Minimum 70% for completed work
        }

        return 85; // Default approval rate for new designers
    }

    // Fallback methods if no data in database
    private function getDefaultTeamLeadData()
    {
        return [
            'id' => 0,
            'name' => 'No Team Lead',
            'position' => 'Team Lead',
            'avatar' => 'TL',
            'performance' => 0,
            'tasks_completed' => 0,
            'total_tasks' => 0,
            'projects_managed' => 0,
            'team_size' => 0,
            'current_project' => 'No Project',
            'status' => 'offline',
            'workload' => 0,
            'last_active' => 'Never',
            'tasks' => []
        ];
    }

    private function getDefaultDeveloperData()
    {
        return [
            'id' => 0,
            'name' => 'No Developer',
            'position' => 'Developer',
            'avatar' => 'DV',
            'performance' => 0,
            'tasks_completed' => 0,
            'total_tasks' => 0,
            'current_project' => 'No Project',
            'specialization' => 'Developer',
            'status' => 'offline',
            'workload' => 0,
            'commits' => 0,
            'code_quality' => 0,
            'tasks' => []
        ];
    }

    private function getDefaultDesignerData()
    {
        return [
            'id' => 0,
            'name' => 'No Designer',
            'position' => 'Designer',
            'avatar' => 'DS',
            'performance' => 0,
            'tasks_completed' => 0,
            'total_tasks' => 0,
            'current_project' => 'No Project',
            'specialization' => 'Designer',
            'status' => 'offline',
            'workload' => 0,
            'designs_created' => 0,
            'client_approval' => 0,
            'tasks' => []
        ];
    }
}