<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeamMonitoringController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HelpRequestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\ProjectMonitoringController;

// Authentication
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    /**
     * Admin routes
     */
    Route::middleware('role:admin')->group(function () {
        // Project management
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'addMember'])->name('projects.members.add');
        Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'removeMember'])->name('projects.members.remove');

        // Monitoring
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('admin.monitoring');
        Route::get('/monitoring/projects', [MonitoringController::class, 'getProjects'])->name('admin.monitoring.projects');
        Route::get('/monitoring/stats', [MonitoringController::class, 'getStats'])->name('admin.monitoring.stats');
        Route::get('/monitoring/data', [MonitoringController::class, 'getMonitoringData'])->name('admin.monitoring.data');
        Route::post('/monitoring/refresh', [MonitoringController::class, 'refreshData'])->name('admin.monitoring.refresh');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::match(['get', 'post'], '/reports/general', [ReportController::class, 'generateGeneralReport'])->name('admin.reports.general');
        Route::post('/reports/project', [ReportController::class, 'generateProjectReport'])->name('admin.reports.project');
        Route::post('/reports/team', [ReportController::class, 'generateTeamReport'])->name('admin.reports.team');

        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Team monitoring
        Route::prefix('admin')->group(function () {
            Route::get('/team-monitoring', [TeamMonitoringController::class, 'index'])->name('admin.team.monitoring');
            Route::get('/team-monitoring/data', [TeamMonitoringController::class, 'getTeamData'])->name('admin.team.monitoring.data');
            Route::post('/team-monitoring/update-task', [TeamMonitoringController::class, 'updateTask'])->name('admin.team.monitoring.update');
        });
    });

    /**
     * Team Lead routes
     */
    Route::middleware('role:team_lead')->group(function () {
        Route::get('/teamlead/dashboard', [ProjectController::class, 'teamLeadDashboard'])->name('team_lead.dashboard');
        Route::get('/teamlead/projects/{project}', [ProjectController::class, 'teamLeadShow'])->name('team_lead.projects.show');

        Route::get('/teamlead/boards/{board}/cards', [CardController::class, 'index'])->name('team_lead.cards.index');
        Route::get('/teamlead/boards/{board}/cards/create', [CardController::class, 'create'])->name('team_lead.cards.create');
        Route::post('/teamlead/boards/{board}/cards', [CardController::class, 'store'])->name('team_lead.cards.store');
        Route::get('/teamlead/boards/{board}/cards/{card}/edit', [CardController::class, 'edit'])->name('team_lead.cards.edit');
        Route::put('/teamlead/boards/{board}/cards/{card}', [CardController::class, 'update'])->name('team_lead.cards.update');
        Route::delete('/teamlead/boards/{board}/cards/{card}', [CardController::class, 'destroy'])->name('team_lead.cards.destroy');

        Route::post('/subtasks/{subtask}/approve', [SubtaskController::class, 'approve'])->name('subtasks.approve');
        Route::post('/subtasks/{subtask}/reject', [SubtaskController::class, 'reject'])->name('subtasks.reject');
    });

    /**
     * Developer & Designer routes
     */
    Route::middleware('role:developer,designer')->group(function () {
        Route::get('/developer/dashboard', [ProjectController::class, 'developerDashboard'])->name('developer.dashboard');
        Route::get('/designer/dashboard', [ProjectController::class, 'designerDashboard'])->name('designer.dashboard');

        Route::get('/cards/{card}/subtasks/create', [SubtaskController::class, 'create'])->name('subtasks.create');
        Route::post('/cards/{card}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
        Route::post('/subtasks/{subtask}/start', [SubtaskController::class, 'start'])->name('subtasks.start');
        Route::post('/subtasks/{subtask}/complete', [SubtaskController::class, 'complete'])->name('subtasks.complete');
    });

    /**
     * Help request routes - accessible by developers, designers, and team leads
     */
    Route::middleware('role:developer,designer,team_lead')->group(function () {
        Route::get('/help-requests', [HelpRequestController::class, 'index'])->name('help-requests.index');
        Route::get('/help-requests/create/{subtask}', [HelpRequestController::class, 'create'])->name('help-requests.create');
        Route::post('/help-requests/{subtask}', [HelpRequestController::class, 'store'])->name('help-requests.store');
        Route::get('/help-requests/{helpRequest}', [HelpRequestController::class, 'show'])->name('help-requests.show');
        Route::post('/help-requests/{helpRequest}/respond', [HelpRequestController::class, 'respond'])->name('help-requests.respond');
        Route::post('/help-requests/{helpRequest}/mark-resolved', [HelpRequestController::class, 'markResolved'])->name('help-requests.mark-resolved');
    });

    /**
     * Card comments - admin & team lead
     */
    Route::middleware('role:admin,team_lead')->group(function () {
        Route::post('/cards/{card}/comments', [CommentController::class, 'storeCardComment'])->name('comments.card.store');
        Route::put('/card-comments/{comment}', [CommentController::class, 'updateCardComment'])->name('comments.card.update');
        Route::delete('/card-comments/{comment}', [CommentController::class, 'deleteCardComment'])->name('comments.card.delete');
    });

    /**
     * Subtask comments - all delivery roles
     */
    Route::middleware('role:admin,team_lead,developer,designer')->group(function () {
        Route::post('/subtasks/{subtask}/comments', [CommentController::class, 'storeSubtaskComment'])->name('comments.subtask.store');
        Route::put('/subtask-comments/{comment}', [CommentController::class, 'updateSubtaskComment'])->name('comments.subtask.update');
        Route::delete('/subtask-comments/{comment}', [CommentController::class, 'deleteSubtaskComment'])->name('comments.subtask.delete');
    });
});
