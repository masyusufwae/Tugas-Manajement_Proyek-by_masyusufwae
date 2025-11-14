<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Board;
use App\Models\User;

class ProjectController extends Controller
{
    /**
     * Dashboard utama (redirect sesuai role user)
     */
    public function index()
    {
        $role = auth()->user()->role;

        switch ($role) {
            case 'admin':
                $projects = Project::with(['boards.cards.subtasks', 'members.user'])->get();
                return view('admin.dashboard', compact('projects'));

            case 'team_lead':
                return $this->teamLeadDashboard();

            case 'developer':
                return $this->developerDashboard();

            case 'designer':
                return $this->designerDashboard();

            default:
                abort(403, 'Role tidak dikenal');
        }
    }

    /**
     * ================= ADMIN =================
     */

    // Form buat proyek
    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        // Ambil semua users yang bisa ditambahkan (team_lead, developer, designer)
        $teamLeads = User::where('role', 'team_lead')->get();
        $developers = User::where('role', 'developer')->get();
        $designers = User::where('role', 'designer')->get();
        
        return view('admin.projects.create', compact('teamLeads', 'developers', 'designers'));
    }

    // Simpan proyek baru
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'project_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'deadline'     => 'nullable|date',
            'team_lead_id' => [
                'required',
                'exists:users,user_id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if ($user && $user->role !== 'team_lead') {
                        $fail('User yang dipilih harus memiliki role Team Lead.');
                    }
                },
            ],
            'developer_ids' => 'nullable|array',
            'developer_ids.*' => 'exists:users,user_id',
            'designer_ids' => 'nullable|array',
            'designer_ids.*' => 'exists:users,user_id',
        ]);

        // Simpan proyek
        $project = Project::create([
            'project_name' => $request->project_name,
            'description'  => $request->description,
            'created_by'   => auth()->id(),
            'deadline'     => $request->deadline
        ]);

        // Masukkan Admin ke project_members
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id'    => auth()->id(),
            'role'       => 'admin',
        ]);

        // Masukkan Team Lead ke project_members
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id'    => $request->team_lead_id,
            'role'       => 'member',
        ]);

        // Masukkan Developers ke project_members
        if ($request->has('developer_ids') && is_array($request->developer_ids)) {
            foreach ($request->developer_ids as $developerId) {
                // Cek apakah user sudah ada di project (jangan duplikat)
                $exists = ProjectMember::where('project_id', $project->project_id)
                    ->where('user_id', $developerId)
                    ->exists();
                
                if (!$exists) {
                    ProjectMember::create([
                        'project_id' => $project->project_id,
                        'user_id'    => $developerId,
                        'role'       => 'member',
                    ]);
                }
            }
        }

        // Masukkan Designers ke project_members
        if ($request->has('designer_ids') && is_array($request->designer_ids)) {
            foreach ($request->designer_ids as $designerId) {
                // Cek apakah user sudah ada di project (jangan duplikat)
                $exists = ProjectMember::where('project_id', $project->project_id)
                    ->where('user_id', $designerId)
                    ->exists();
                
                if (!$exists) {
                    ProjectMember::create([
                        'project_id' => $project->project_id,
                        'user_id'    => $designerId,
                        'role'       => 'member',
                    ]);
                }
            }
        }

        // Buat boards default
        $boards = ['To Do', 'In Progress', 'Review', 'Done'];
        foreach ($boards as $i => $board) {
            Board::create([
                'project_id' => $project->project_id,
                'board_name' => $board,
                'position'   => $i + 1
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Project berhasil dibuat!');
    }

    // Detail proyek (Admin)
    public function show(Project $project)
    {
        $project = Project::with([
                        'boards.cards.subtasks.comments',
                        'boards.cards.comments',
                        'members.user'
                    ])
                    ->findOrFail($project->project_id);

        if (auth()->user()->role !== 'admin') abort(403);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * ================= TEAM LEAD =================
     */

    // Dashboard Team Lead
    public function teamLeadDashboard()
    {
        $userId = auth()->id();

        $projects = Project::whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['boards.cards.subtasks', 'members.user'])->get();

        return view('team_lead.dashboard', compact('projects'));
    }

    // Detail proyek Team Lead
    public function teamLeadShow(Project $project)
    {
        $userId = auth()->id();

        $project = Project::where('project_id', $project->project_id)
            ->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with([
                'boards.cards.subtasks.comments',
                'boards.cards.comments',
                'members.user'
            ])
            ->firstOrFail();

        return view('team_lead.projects.show', compact('project'));
    }

    /**
     * ================= DEVELOPER =================
     */
   public function developerDashboard()
{
    $userId = auth()->id();

    // ambil semua cards yang di-assign ke developer ini
    $cards = \App\Models\Card::whereHas('assignments', function($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->with(['board.project.members.user', 'board.project.boards.cards.subtasks', 'subtasks.comments', 'subtasks.helpRequests' => function($query) use ($userId) {
        $query->where('requester_id', $userId);
    }])
    ->get();

    return view('developer.dashboard', compact('cards'));
}


    /**
     * ================= DESIGNER =================
     */
   public function designerDashboard()
{
    $userId = auth()->id();

    // ambil semua cards yang di-assign ke designer ini
    $cards = \App\Models\Card::whereHas('assignments', function($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->with(['board.project.members.user', 'board.project.boards.cards.subtasks', 'subtasks.comments', 'subtasks.helpRequests' => function($query) use ($userId) {
        $query->where('requester_id', $userId);
    }])
    ->get();

    return view('designer.dashboard', compact('cards'));
}

}
