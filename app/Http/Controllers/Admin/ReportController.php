<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Project;
use App\Models\Card;
use App\Models\Subtask;
use App\Models\User;
use App\Models\TimeLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function generateGeneralReport(Request $request)
    {
        $payload = $request->all();
        $payload['format'] = $payload['format'] ?? 'pdf';

        $validator = Validator::make($payload, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel'
        ]);

        if ($validator->fails()) {
            if ($request->isMethod('get')) {
                abort(422, $validator->errors()->first());
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = $payload['start_date'] ?? null;
        $endDate = $payload['end_date'] ?? null;

        $reportData = $this->generateGeneralData($startDate, $endDate);

        if ($payload['format'] === 'pdf') {
            if (!$this->isPdfAvailable()) {
                return $this->pdfUnavailableResponse($request);
            }

            $pdf = Pdf::loadView('admin.reports.general-pdf', $reportData);
            $filename = 'general-report-' . now()->format('Y-m-d') . '.pdf';

            return $request->boolean('print')
                ? $pdf->stream($filename)
                : $pdf->download($filename);
        }

        return $this->generateGeneralExcel($reportData);
    }

    public function generateProjectReport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel'
        ]);

        $project = Project::with(['boards.cards.subtasks', 'boards.cards.assignments.user'])
            ->findOrFail($request->project_id);

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $startBoundary = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endBoundary = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // Filter data berdasarkan tanggal jika ada
        $cards = $project->boards->flatMap(function($board) {
            return $board->cards;
        });

        if ($startBoundary) {
            $cards = $cards->filter(function($card) use ($startBoundary) {
                return $card->created_at && $card->created_at->greaterThanOrEqualTo($startBoundary);
            });
        }

        if ($endBoundary) {
            $cards = $cards->filter(function($card) use ($endBoundary) {
                return $card->created_at && $card->created_at->lessThanOrEqualTo($endBoundary);
            });
        }

        $reportData = $this->generateProjectData($project, $cards);

        if ($request->format === 'pdf') {
            if (!$this->isPdfAvailable()) {
                return $this->pdfUnavailableResponse($request);
            }
            return $this->generatePDF($reportData, $project);
        } else {
            return $this->generateExcel($reportData, $project);
        }
    }

    public function generateTeamReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $reportData = $this->generateTeamData($startDate, $endDate);

        if ($request->format === 'pdf') {
            if (!$this->isPdfAvailable()) {
                return $this->pdfUnavailableResponse($request);
            }
            return $this->generateTeamPDF($reportData);
        } else {
            return $this->generateTeamExcel($reportData);
        }
    }

    private function generateProjectData($project, $cards)
    {
        $totalCards = $cards->count();
        $completedCards = $cards->where('status', 'done')->count();
        $inProgressCards = $cards->where('status', 'in_progress')->count();
        $reviewCards = $cards->where('status', 'review')->count();
        $todoCards = $cards->where('status', 'todo')->count();

        $totalSubtasks = $cards->sum(function($card) {
            return $card->subtasks->count();
        });

        $completedSubtasks = $cards->sum(function($card) {
            return $card->subtasks->where('status', 'done')->count();
        });

        $totalEstimatedHours = $cards->sum('estimated_hours');
        $totalActualHours = $cards->sum('actual_hours');

        $teamMembers = $project->boards->flatMap(function($board) {
            return $board->cards->flatMap(function($card) {
                return $card->assignments->map(function($assignment) {
                    return $assignment->user;
                });
            });
        })->unique('user_id');

        return [
            'project' => $project,
            'summary' => [
                'total_cards' => $totalCards,
                'completed_cards' => $completedCards,
                'in_progress_cards' => $inProgressCards,
                'review_cards' => $reviewCards,
                'todo_cards' => $todoCards,
                'total_subtasks' => $totalSubtasks,
                'completed_subtasks' => $completedSubtasks,
                'total_estimated_hours' => $totalEstimatedHours,
                'total_actual_hours' => $totalActualHours,
                'completion_percentage' => $totalCards > 0 ? round(($completedCards / $totalCards) * 100, 2) : 0
            ],
            'cards' => $cards,
            'team_members' => $teamMembers,
            'generated_at' => now()
        ];
    }

    private function generateTeamData($startDate, $endDate)
    {
        $query = DB::table('users')
            ->leftJoin('card_assignments', 'users.user_id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.card_id')
            ->leftJoin('subtasks', 'cards.card_id', '=', 'subtasks.card_id')
            ->leftJoin('time_logs', 'subtasks.subtask_id', '=', 'time_logs.subtask_id')
            ->select(
                'users.user_id',
                'users.username',
                'users.full_name',
                'users.role',
                DB::raw('COUNT(DISTINCT cards.card_id) as total_cards'),
                DB::raw('COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.card_id END) as completed_cards'),
                DB::raw('COUNT(DISTINCT subtasks.subtask_id) as total_subtasks'),
                DB::raw('COUNT(DISTINCT CASE WHEN subtasks.status = "done" THEN subtasks.subtask_id END) as completed_subtasks'),
                DB::raw('SUM(COALESCE(time_logs.duration_minutes, 0)) / 60 as total_hours_logged')
            )
            ->groupBy('users.user_id', 'users.username', 'users.full_name', 'users.role');

        if ($startDate) {
            $query->where('cards.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('cards.created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $teamData = $query->get();

        return [
            'team_data' => $teamData,
            'summary' => [
                'total_users' => $teamData->count(),
                'total_hours_logged' => $teamData->sum('total_hours_logged'),
                'average_completion' => $teamData->avg(function($user) {
                    return $user->total_cards > 0 ? ($user->completed_cards / $user->total_cards) * 100 : 0;
                })
            ],
            'generated_at' => now()
        ];
    }

    private function generateGeneralData(?string $startDate, ?string $endDate): array
    {
        $cardQuery = Card::with('subtasks');

        if ($startDate) {
            $cardQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $cardQuery->whereDate('created_at', '<=', $endDate);
        }

        $cards = $cardQuery->get();

        $statusCounts = [
            'todo' => $cards->where('status', 'todo')->count(),
            'in_progress' => $cards->where('status', 'in_progress')->count(),
            'review' => $cards->where('status', 'review')->count(),
            'done' => $cards->where('status', 'done')->count(),
        ];

        $subtasks = $cards->flatMap(function($card) {
            return $card->subtasks;
        });

        $timeLogQuery = TimeLog::query();

        if ($startDate) {
            $timeLogQuery->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $timeLogQuery->whereDate('start_time', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $projectBreakdown = Project::with(['boards.cards' => function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('cards.created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('cards.created_at', '<=', $endDate);
            }
        }])->get()->map(function ($project) {
            $cards = $project->boards->flatMap(function ($board) {
                return $board->cards;
            });

            return [
                'name' => $project->project_name,
                'total_cards' => $cards->count(),
                'completed_cards' => $cards->where('status', 'done')->count(),
                'in_progress_cards' => $cards->where('status', 'in_progress')->count(),
                'review_cards' => $cards->where('status', 'review')->count(),
            ];
        })->filter(function ($summary) {
            return $summary['total_cards'] > 0;
        })->sortByDesc('total_cards')->values();

        $generalSummary = [
            'total_projects' => Project::when($startDate, function($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })->when($endDate, function($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })->count(),
            'total_cards' => $cards->count(),
            'status_counts' => $statusCounts,
            'total_subtasks' => $subtasks->count(),
            'completed_subtasks' => $subtasks->where('status', 'done')->count(),
            'total_estimated_hours' => round($cards->sum(function ($card) {
                return $card->estimated_hours ?? 0;
            }), 2),
            'total_actual_hours' => round($cards->sum(function ($card) {
                return $card->actual_hours ?? 0;
            }), 2),
            'total_logged_hours' => round(($timeLogQuery->sum('duration_minutes') ?? 0) / 60, 2),
        ];

        $generalSummary['completion_percentage'] = $generalSummary['total_cards'] > 0
            ? round(($statusCounts['done'] / $generalSummary['total_cards']) * 100, 2)
            : 0;

        $teamData = $this->generateTeamData($startDate, $endDate);

        return [
            'summary' => $generalSummary,
            'project_breakdown' => $projectBreakdown,
            'team_data' => $teamData['team_data'],
            'team_summary' => $teamData['summary'],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'generated_at' => now(),
        ];
    }

    private function generateGeneralExcel(array $reportData)
    {
        $filename = 'general-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['General Report']);
            fputcsv($file, ['Generated at', $reportData['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Projects', $reportData['summary']['total_projects']]);
            fputcsv($file, ['Total Cards', $reportData['summary']['total_cards']]);
            fputcsv($file, ['Cards - To Do', $reportData['summary']['status_counts']['todo']]);
            fputcsv($file, ['Cards - In Progress', $reportData['summary']['status_counts']['in_progress']]);
            fputcsv($file, ['Cards - Review', $reportData['summary']['status_counts']['review']]);
            fputcsv($file, ['Cards - Done', $reportData['summary']['status_counts']['done']]);
            fputcsv($file, ['Completion (%)', $reportData['summary']['completion_percentage']]);
            fputcsv($file, ['Total Subtasks', $reportData['summary']['total_subtasks']]);
            fputcsv($file, ['Completed Subtasks', $reportData['summary']['completed_subtasks']]);
            fputcsv($file, ['Estimated Hours', $reportData['summary']['total_estimated_hours']]);
            fputcsv($file, ['Actual Hours', $reportData['summary']['total_actual_hours']]);
            fputcsv($file, ['Logged Hours', $reportData['summary']['total_logged_hours']]);
            fputcsv($file, []);

            if ($reportData['project_breakdown']->isNotEmpty()) {
                fputcsv($file, ['PROJECT BREAKDOWN']);
                fputcsv($file, ['Project', 'Total Cards', 'In Progress', 'Review', 'Done']);

                foreach ($reportData['project_breakdown'] as $project) {
                    fputcsv($file, [
                        $project['name'],
                        $project['total_cards'],
                        $project['in_progress_cards'],
                        $project['review_cards'],
                        $project['completed_cards'],
                    ]);
                }

                fputcsv($file, []);
            }

            if ($reportData['team_data']->isNotEmpty()) {
                fputcsv($file, ['TEAM PERFORMANCE']);
                fputcsv($file, ['Name', 'Username', 'Role', 'Cards', 'Cards Completed', 'Subtasks', 'Subtasks Completed', 'Hours Logged']);

                foreach ($reportData['team_data'] as $member) {
                    fputcsv($file, [
                        $member->full_name,
                        $member->username,
                        $member->role,
                        $member->total_cards,
                        $member->completed_cards,
                        $member->total_subtasks,
                        $member->completed_subtasks,
                        $member->total_hours_logged,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    private function generatePDF($reportData, $project)
    {
        if (!$this->isPdfAvailable()) {
            abort(501, 'Fitur PDF belum tersedia. Install barryvdh/laravel-dompdf atau gunakan format lain.');
        }

        $pdf = Pdf::loadView('admin.reports.project-pdf', $reportData);
        return $pdf->download('project-report-' . $project->project_name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    private function generateExcel($reportData, $project)
    {
        // Implement Excel generation using Laravel Excel package
        // For now, return a simple CSV
        $filename = 'project-report-' . $project->project_name . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Project Report - ' . $reportData['project']->project_name]);
            fputcsv($file, ['Generated at: ' . $reportData['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Cards', $reportData['summary']['total_cards']]);
            fputcsv($file, ['Completed Cards', $reportData['summary']['completed_cards']]);
            fputcsv($file, ['In Progress Cards', $reportData['summary']['in_progress_cards']]);
            fputcsv($file, ['Review Cards', $reportData['summary']['review_cards']]);
            fputcsv($file, ['Todo Cards', $reportData['summary']['todo_cards']]);
            fputcsv($file, ['Completion Percentage', $reportData['summary']['completion_percentage'] . '%']);
            fputcsv($file, []);
            
            // Cards details
            fputcsv($file, ['CARDS DETAILS']);
            fputcsv($file, ['Card Title', 'Status', 'Priority', 'Estimated Hours', 'Actual Hours', 'Created At']);
            
            foreach ($reportData['cards'] as $card) {
                fputcsv($file, [
                    $card->card_title,
                    $card->status,
                    $card->priority,
                    $card->estimated_hours,
                    $card->actual_hours,
                    $card->created_at
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generateTeamPDF($reportData)
    {
        if (!$this->isPdfAvailable()) {
            abort(501, 'Fitur PDF belum tersedia. Install barryvdh/laravel-dompdf atau gunakan format lain.');
        }

        $pdf = Pdf::loadView('admin.reports.team-pdf', $reportData);
        return $pdf->download('team-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function generateTeamExcel($reportData)
    {
        $filename = 'team-report-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Team Performance Report']);
            fputcsv($file, ['Generated at: ' . $reportData['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            // Team data
            fputcsv($file, ['TEAM MEMBER PERFORMANCE']);
            fputcsv($file, ['Username', 'Full Name', 'Role', 'Total Cards', 'Completed Cards', 'Total Subtasks', 'Completed Subtasks', 'Hours Logged']);
            
            foreach ($reportData['team_data'] as $user) {
                fputcsv($file, [
                    $user->username,
                    $user->full_name,
                    $user->role,
                    $user->total_cards,
                    $user->completed_cards,
                    $user->total_subtasks,
                    $user->completed_subtasks,
                    $user->total_hours_logged
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    private function isPdfAvailable(): bool
    {
        return class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);
    }

    private function pdfUnavailableResponse(Request $request)
    {
        $message = 'Fitur export PDF belum tersedia karena paket DomPDF belum diinstal. Jalankan "composer require barryvdh/laravel-dompdf" atau gunakan format Excel.';

        if ($request->isMethod('get') || $request->boolean('print')) {
            abort(501, $message);
        }

        return redirect()->back()->with('error', $message)->withInput();
    }
}
