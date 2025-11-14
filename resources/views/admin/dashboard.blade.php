@extends('admin.sidebar')

@section('title', 'Admin Dashboard')

@section('content')
            <h2>Daftar Proyek</h2>
            <a href="{{ route('projects.create') }}" class="btn btn-success mb-3">+ Buat Proyek Baru</a>

            <div class="table-container">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Proyek</th>
                            <th>Anggota</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($projects as $i => $project)
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
                            $progressClass = $progress >= 70 ? 'bg-success' : ($progress >= 30 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $project->project_name }}</td>
                            <td>
                                @forelse($project->members as $member)
                                    <span class="badge bg-primary">{{ $member->user->full_name ?? $member->user->username }}</span>
                                @empty
                                    <span class="text-muted">Belum ada</span>
                                @endforelse
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $progressClass }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%">
                                        {{ $progress }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $completedSubtasks }}/{{ $totalSubtasks }} subtasks</small>
                            </td>
                            <td>{{ $project->deadline }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
@endsection