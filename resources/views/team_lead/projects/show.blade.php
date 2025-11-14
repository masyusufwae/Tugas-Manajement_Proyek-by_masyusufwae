@extends('team_lead.sidebar')

@section('title', 'Detail Proyek')

@section('content')
    <div class="py-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div class="flex-grow-1">
                        <h2 class="mb-1 text-primary">{{ $project->project_name }}</h2>
                        <p class="text-muted mb-1">{{ $project->description }}</p>
                        <small class="text-secondary d-block">Deadline: {{ $project->deadline ?? '-' }}</small>
                    </div>
                    <a href="{{ route('team_lead.dashboard') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="row">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div>
                            <strong>Anggota Tim:</strong>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @forelse($project->members as $member)
                                    <span class="badge bg-primary">{{ $member->user->full_name ?? $member->user->username }}</span>
                                @empty
                                    <span class="text-muted">Belum ada anggota</span>
                                @endforelse
                            </div>
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
                            @if($board->board_name === 'To Do')
                                <a href="{{ route('team_lead.cards.create', $board->board_id) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @forelse($board->cards as $card)
                                <div class="border rounded-3 bg-white p-3 mb-3">
                                    <h5 class="mb-1">{{ $card->card_title }}</h5>
                                    <p class="text-muted small mb-2">{{ $card->description ?: 'Tidak ada deskripsi' }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary text-uppercase">{{ $card->status }}</span>
                                        <span class="badge bg-warning text-dark text-capitalize">{{ $card->priority }}</span>
                                    </div>
                                    <a href="{{ route('team_lead.cards.index', $board->board_id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-kanban"></i> Kelola Cards
                                    </a>
                                </div>
                            @empty
                                <p class="text-muted">Belum ada card di board ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Belum ada board pada proyek ini.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
