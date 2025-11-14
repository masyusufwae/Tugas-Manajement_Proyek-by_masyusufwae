@extends('admin.sidebar')

@section('title', 'Detail Proyek')

@section('content')
    <div class="py-4">
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

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="card-title text-primary fw-bold mb-2">{{ $project->project_name }}</h2>
                        <p class="text-muted mb-1">{{ $project->description }}</p>
                        <small class="text-secondary">Deadline: {{ $project->deadline }}</small>
                    </div>
                    <div class="col-md-4">
                        @php
                            // Hitung progress berdasarkan subtask yang sudah done
                            $totalSubtasks = 0;
                            $completedSubtasks = 0;
                            foreach ($project->boards as $board) {
                                foreach ($board->cards as $card) {
                                    foreach ($card->subtasks as $subtask) {
                                        $totalSubtasks++;
                                        if ($subtask->status === 'done') {
                                            $completedSubtasks++;
                                        }
                                    }
                                }
                            }
                            $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <strong>Progress:</strong>
                            <div class="progress mt-1">
                                <div class="progress-bar {{ $progress >= 70 ? 'bg-success' : ($progress >= 30 ? 'bg-warning' : 'bg-danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $progress }}%">
                                    {{ $progress }}%
                                </div>
                            </div>
                            <small class="text-muted">{{ $completedSubtasks }} / {{ $totalSubtasks }} subtasks selesai</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Anggota Tim:</strong>
                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addMemberForm">
                            <i class="bi bi-person-plus"></i> Tambah Anggota
                        </button>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        @forelse($project->members as $member)
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary">
                                    {{ $member->user->full_name ?? $member->user->username }}
                                </span>
                                <span class="badge bg-light text-dark text-capitalize">{{ $member->role }}</span>

                                @if($member->role !== 'admin')
                                    <form action="{{ route('projects.members.remove', [$project->project_id, $member->member_id]) }}" method="POST" onsubmit="return confirm('Hapus anggota ini dari proyek?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-dash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <span class="text-muted">Belum ada anggota</span>
                        @endforelse
                    </div>
                    
                    <div class="collapse mt-3" id="addMemberForm">
                        <div class="card card-body border-0 shadow-sm">
                            <h6 class="mb-3">Tambah Anggota Baru</h6>
                            <form action="{{ route('projects.members.add', $project->project_id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username anggota" required>
                                    <small class="text-muted">Masukkan username user yang akan ditambahkan ke proyek</small>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#addMemberForm">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Tambah
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse($project->boards as $board)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ $board->board_name }}</span>
                            <span class="badge bg-secondary">{{ $board->cards->count() }} cards</span>
                        </div>
                        <div class="card-body">
                            @forelse($board->cards as $card)
                                <div class="mb-4 pb-4 border-bottom border-light">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">{{ $card->card_title }}</h5>
                                            <p class="text-muted small mb-2">{{ $card->description ?: 'Tidak ada deskripsi' }}</p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary text-uppercase">{{ $card->status }}</span>
                                            <span class="badge bg-warning text-dark text-capitalize">{{ $card->priority }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                        <strong class="mb-0">Komentar Card</strong>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminCardComments{{ $card->card_id }}">
                                                <i class="bi bi-chat-dots"></i> Tampilkan Komentar
                                            </button>
                                            @if(in_array(auth()->user()->role, ['admin', 'team_lead']))
                                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminCardCommentForm{{ $card->card_id }}">
                                                    <i class="bi bi-pencil-square"></i> Tambah Komentar
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="collapse mb-3" id="adminCardCommentForm{{ $card->card_id }}">
                                        <form action="{{ route('comments.card.store', $card->card_id) }}" method="POST" class="card card-body border-0 shadow-sm">
                                            @csrf
                                            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis komentar..." required></textarea>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#adminCardCommentForm{{ $card->card_id }}">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-send"></i> Kirim
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="collapse" id="adminCardComments{{ $card->card_id }}">
                                        @forelse($card->comments as $comment)
                                            @include('components.card-comment-thread', [
                                                'comment' => $comment,
                                                'card' => $card,
                                                'depth' => 0,
                                                'canReply' => in_array(auth()->user()->role, ['admin', 'team_lead']),
                                            ])
                                        @empty
                                            <p class="text-muted mt-2">Belum ada komentar.</p>
                                        @endforelse
                                    </div>

                                    @if($card->subtasks->isNotEmpty())
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-semibold">Subtasks</span>
                                                <span class="badge bg-secondary">{{ $card->subtasks->count() }}</span>
                                            </div>

                                            @foreach($card->subtasks as $subtask)
                                                <div class="border rounded-3 p-3 mb-3 bg-white">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1">{{ $subtask->subtask_title }}</h6>
                                                            <p class="text-muted small mb-0">{{ $subtask->description ?: '-' }}</p>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-primary text-uppercase">{{ $subtask->status }}</span>
                                                            <small class="text-muted d-block">Estimasi: {{ $subtask->estimated_hours ?? '-' }}</small>
                                                            <small class="text-muted">Aktual: {{ $subtask->actual_hours ?? '-' }}</small>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 bg-light rounded p-2">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                                            <strong class="mb-0">Komentar Subtask</strong>
                                                            <div class="d-flex gap-2">
                                                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminSubtaskComments{{ $subtask->subtask_id }}">
                                                                    <i class="bi bi-chat-dots"></i> Tampilkan Komentar
                                                                </button>
                                                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminSubtaskCommentForm{{ $subtask->subtask_id }}">
                                                                    <i class="bi bi-pencil-square"></i> Tambah Komentar
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="collapse" id="adminSubtaskCommentForm{{ $subtask->subtask_id }}">
                                                            <form action="{{ route('comments.subtask.store', $subtask->subtask_id) }}" method="POST" class="card card-body border-0 shadow-sm mb-3">
                                                                @csrf
                                                                <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tambahkan catatan..." required></textarea>
                                                                <div class="d-flex justify-content-end gap-2">
                                                                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#adminSubtaskCommentForm{{ $subtask->subtask_id }}">Batal</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                                        <i class="bi bi-send"></i> Kirim
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>

                                                        <div class="collapse" id="adminSubtaskComments{{ $subtask->subtask_id }}">
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
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted">Belum ada card di board ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Belum ada board untuk proyek ini.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
