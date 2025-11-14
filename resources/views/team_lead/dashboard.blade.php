@extends('team_lead.sidebar')

@section('title', 'Team Lead Dashboard')

@section('content')
            <h2>Proyek yang Anda Pimpin</h2>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Proyek</th>
                        <th>Deadline</th>
                        <th>Progress</th>
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
                        <td>
                            <strong>{{ $project->project_name }}</strong>
                            <div class="mt-1">
                                <small class="text-muted">Anggota: 
                                    @forelse($project->members as $member)
                                        {{ $member->user->full_name ?? $member->user->username }}@if(!$loop->last), @endif
                                    @empty
                                        <span class="text-muted">Belum ada</span>
                                    @endforelse
                                </small>
                            </div>
                        </td>
                        <td>{{ $project->deadline }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" 
                                     data-bs-toggle="tooltip" 
                                     title="{{ $completedSubtasks }} / {{ $totalSubtasks }} subtasks selesai">
                                    <div class="progress-bar {{ $progressClass }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%;" 
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <span class="progress-text">{{ $progress }}%</span>
                                    </div>
                                </div>
                                <small class="progress-details">{{ $completedSubtasks }}/{{ $totalSubtasks }}</small>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('team_lead.projects.show',$project->project_id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
@endsection

@section('styles')
<style>
    .progress {
        height: 20px;
        background-color: #e9ecef;
        border-radius: 4px;
    }
    .progress-bar {
        transition: width 0.5s ease;
    }
    .progress-text {
        font-size: 12px;
        font-weight: bold;
        color: #000;
        text-shadow: 0 0 2px #fff;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .progress-details {
        font-size: 12px;
        color: #6c757d;
    }
</style>
@endsection

@section('scripts')
<script>
    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection