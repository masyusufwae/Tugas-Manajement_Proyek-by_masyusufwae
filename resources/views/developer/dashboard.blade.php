@extends('developer.sidebar')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Dashboard Developer')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-person-workspace"></i> Dashboard Developer</h2>
            <p class="text-muted mb-0">Pantau card dan subtasks yang sedang kamu kerjakan</p>
        </div>
        <a href="{{ route('help-requests.index') }}" class="btn btn-info text-white">
            <i class="bi bi-tools"></i> Solver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($cards->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada tugas untuk Anda.
        </div>
    @else
        @php
            // Group cards by project
            $projectsData = [];
            foreach ($cards as $card) {
                $project = $card->board->project;
                if ($project && !isset($projectsData[$project->project_id])) {
                    // Calculate progress
                    $totalSubtasks = 0;
                    $completedSubtasks = 0;
                    foreach ($project->boards as $board) {
                        foreach ($board->cards as $pCard) {
                            foreach ($pCard->subtasks as $subtask) {
                                $totalSubtasks++;
                                if ($subtask->status === 'done') {
                                    $completedSubtasks++;
                                }
                            }
                        }
                    }
                    $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                    
                    $projectsData[$project->project_id] = [
                        'project' => $project,
                        'progress' => $progress,
                        'completedSubtasks' => $completedSubtasks,
                        'totalSubtasks' => $totalSubtasks
                    ];
                }
            }
        @endphp
        
        @if(!empty($projectsData))
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-folder"></i> Informasi Proyek</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($projectsData as $projData)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-2">{{ $projData['project']->project_name }}</h6>
                                    <div class="mb-2">
                                        <strong>Progress:</strong>
                                        <div class="progress mt-1">
                                            <div class="progress-bar {{ $projData['progress'] >= 70 ? 'bg-success' : ($projData['progress'] >= 30 ? 'bg-warning' : 'bg-danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $projData['progress'] }}%">
                                                {{ $projData['progress'] }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $projData['completedSubtasks'] }}/{{ $projData['totalSubtasks'] }} subtasks selesai</small>
                                    </div>
                                    <div>
                                        <strong>Anggota Tim:</strong>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @forelse($projData['project']->members as $member)
                                                <span class="badge bg-primary">{{ $member->user->full_name ?? $member->user->username }}</span>
                                            @empty
                                                <span class="text-muted">Belum ada</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Your Assigned Cards</span>
                <span class="badge bg-secondary">{{ $cards->count() }} card</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Board</th>
                                <th>Card</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Subtasks</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                                <tr>
                                    <td>{{ $card->board->project->project_name ?? '-' }}</td>
                                    <td>{{ $card->board->board_name }}</td>
                                    <td>
                                        <strong>{{ $card->card_title }}</strong>
                                        @if($card->description)
                                            <br><small class="text-muted">{{ Str::limit($card->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($card->priority === 'high')
                                            <span class="badge bg-danger">High</span>
                                        @elseif($card->priority === 'medium')
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-secondary">Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($card->status === 'todo')
                                            <span class="badge bg-secondary">To Do</span>
                                        @elseif($card->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @elseif($card->status === 'review')
                                            <span class="badge bg-info text-dark">Review</span>
                                        @else
                                            <span class="badge bg-success">Done</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $card->subtasks->count() }} subtasks</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#subtasksModal{{ $card->card_id }}">
                                            <i class="bi bi-list-task"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @foreach($cards as $card)
        <div class="modal fade" id="subtasksModal{{ $card->card_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Subtasks - {{ $card->card_title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>Status Card:</strong>
                                <span class="badge bg-secondary text-uppercase">{{ $card->status }}</span>
                            </div>
                            <a href="{{ route('subtasks.create', $card->card_id) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> Tambah Subtask
                            </a>
                        </div>

                        @if($card->subtasks->isEmpty())
                            <p class="text-muted">Belum ada subtask pada card ini.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Estimasi (jam)</th>
                                            <th>Aktual (jam)</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($card->subtasks as $subtask)
                                            <tr>
                                                <td>
                                                    <strong>{{ $subtask->subtask_title }}</strong>
                                                    @if($subtask->description)
                                                        <br><small class="text-muted">{{ $subtask->description }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subtask->status === 'todo')
                                                        <span class="badge bg-secondary">To Do</span>
                                                    @elseif($subtask->status === 'in_progress')
                                                        <span class="badge bg-primary">In Progress</span>
                                                    @elseif($subtask->status === 'review')
                                                        <span class="badge bg-info text-dark">Review</span>
                                                    @else
                                                        <span class="badge bg-success">Done</span>
                                                    @endif
                                                </td>
                                                <td>{{ $subtask->estimated_hours ?? '-' }}</td>
                                                <td>{{ $subtask->actual_hours ?? '-' }}</td>
                                                <td>
                                                    @if($subtask->status === 'todo')
                                                        <form action="{{ route('subtasks.start', $subtask->subtask_id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary btn-sm">Mulai</button>
                                                        </form>
                                                    @elseif($subtask->status === 'in_progress')
                                                        <form action="{{ route('subtasks.complete', $subtask->subtask_id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm">Selesai</button>
                                                        </form>
                                                    @endif

                                                    @php
                                                        $hasPendingRequest = $subtask->helpRequests
                                                            ->where('requester_id', auth()->id())
                                                            ->whereIn('status', ['pending', 'responded'])
                                                            ->isNotEmpty();
                                                    @endphp
                                                    @if($subtask->status !== 'done' && !$hasPendingRequest)
                                                        <a href="{{ route('help-requests.create', $subtask->subtask_id) }}" class="btn btn-warning btn-sm">
                                                            <i class="bi bi-tools"></i> Solver
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="5">
                                                <div class="bg-light rounded p-3">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                                        <strong class="mb-0">Komentar</strong>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#developerSubtaskComments{{ $subtask->subtask_id }}">
                                                                <i class="bi bi-chat-dots"></i> Tampilkan Komentar
                                                            </button>
                                                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#developerSubtaskCommentForm{{ $subtask->subtask_id }}">
                                                                <i class="bi bi-pencil-square"></i> Tambah Komentar
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="collapse" id="developerSubtaskCommentForm{{ $subtask->subtask_id }}">
                                                        <form action="{{ route('comments.subtask.store', $subtask->subtask_id) }}" method="POST" class="card card-body border-0 shadow-sm mb-3">
                                                            @csrf
                                                            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis komentar..." required></textarea>
                                                            <div class="d-flex justify-content-end gap-2">
                                                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#developerSubtaskCommentForm{{ $subtask->subtask_id }}">Batal</button>
                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    <i class="bi bi-send"></i> Kirim
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="collapse" id="developerSubtaskComments{{ $subtask->subtask_id }}">
                                                        @forelse($subtask->comments as $comment)
                                                            @include('components.subtask-comment-thread', [
                                                                'comment' => $comment,
                                                                'subtask' => $subtask,
                                                                'depth' => 0,
                                                            ])
                                                        @empty
                                                            <p class="text-muted mt-2">Belum ada komentar.</p>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
