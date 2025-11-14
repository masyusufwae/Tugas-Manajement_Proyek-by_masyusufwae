@extends('team_lead.sidebar')

@section('title', 'Cards - ' . $board->board_name)

@section('styles')
<style>
    .comment-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }
    .comment-actions button {
        font-size: 12px;
        padding: 2px 8px;
    }
    .subtask-comments {
        background-color: #fff7e6;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Board: {{ $board->board_name }}</h2>
            <p class="text-muted mb-0">Kelola card dan review progres subtasks</p>
        </div>
        <a href="{{ route('team_lead.cards.create', $board->board_id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Card
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

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Daftar Cards</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul</th>
                            <th>Prioritas</th>
                            <th>Estimasi (jam)</th>
                            <th>Status</th>
                            <th>Anggota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td>
                                    <strong>{{ $card->card_title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $card->description ?: 'Tidak ada deskripsi' }}</small>
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
                                <td>{{ $card->estimated_hours ?? '-' }}</td>
                                <td>
                                    @if($card->status === 'todo')
                                        <span class="badge bg-secondary">To Do</span>
                                    @elseif($card->status === 'in_progress')
                                        <span class="badge bg-primary">In Progress</span>
                                    @elseif($card->status === 'review')
                                        <span class="badge bg-info text-dark">Review</span>
                                    @elseif($card->status === 'done')
                                        <span class="badge bg-success">Done</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($card->assignments as $assignment)
                                        <span class="badge bg-info text-dark">{{ $assignment->user->username }}</span>
                                    @empty
                                        <span class="text-muted">Belum ada anggota</span>
                                    @endforelse
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info mb-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $card->card_id }}">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                    @if($card->status !== 'done')
                                        <a href="{{ route('team_lead.cards.edit', [$board->board_id, $card->card_id]) }}" class="btn btn-sm btn-warning mb-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('team_lead.cards.destroy', [$board->board_id, $card->card_id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Hapus card ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle"></i> Belum ada card di board ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach($cards as $card)
        <div class="modal fade" id="detailModal{{ $card->card_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Card: {{ $card->card_title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-secondary text-uppercase">{{ $card->status }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Prioritas:</strong>
                                <span class="badge bg-dark text-capitalize">{{ $card->priority }}</span>
                            </div>
                        </div>
                        <p class="mb-4">{{ $card->description ?: 'Tidak ada deskripsi' }}</p>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                            <h6 class="fw-bold mb-0">Komentar Card</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#cardComments{{ $card->card_id }}">
                                    <i class="bi bi-chat-dots"></i> Tampilkan Komentar
                                </button>
                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#cardCommentForm{{ $card->card_id }}">
                                    <i class="bi bi-pencil-square"></i> Tambah Komentar
                                </button>
                            </div>
                        </div>
                        <div class="collapse mb-4" id="cardCommentForm{{ $card->card_id }}">
                            <form action="{{ route('comments.card.store', $card->card_id) }}" method="POST" class="card card-body border-0 shadow-sm">
                                @csrf
                                <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Tulis catatan untuk card ini..." required></textarea>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#cardCommentForm{{ $card->card_id }}">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-send"></i> Kirim
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="collapse mb-3" id="cardComments{{ $card->card_id }}">
                            @forelse($card->comments as $comment)
                                @include('components.card-comment-thread', [
                                    'comment' => $comment,
                                    'card' => $card,
                                    'canReply' => true,
                                ])
                            @empty
                                <p class="text-muted">Belum ada komentar.</p>
                            @endforelse
                        </div>

                        <h6 class="fw-bold">Subtasks ({{ $card->subtasks->count() }})</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Estimasi</th>
                                        <th>Aktual</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($card->subtasks as $subtask)
                                        <tr>
                                            <td>
                                                <strong>{{ $subtask->subtask_title }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $subtask->description ?: '-' }}</small>
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
                                                @if($subtask->status === 'review')
                                                    <form action="{{ route('subtasks.approve', $subtask->subtask_id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm mb-1">Approve</button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#rejectSubtaskModal{{ $subtask->subtask_id }}">
                                                        Reject
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <div class="bg-light rounded p-3">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                                        <strong class="mb-0">Komentar Subtask</strong>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#subtaskComments{{ $subtask->subtask_id }}">
                                                                <i class="bi bi-chat-dots"></i> Tampilkan Komentar
                                                            </button>
                                                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#subtaskCommentForm{{ $subtask->subtask_id }}">
                                                                <i class="bi bi-pencil-square"></i> Tambah Komentar
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="collapse" id="subtaskCommentForm{{ $subtask->subtask_id }}">
                                                        <form action="{{ route('comments.subtask.store', $subtask->subtask_id) }}" method="POST" class="card card-body border-0 shadow-sm mb-3">
                                                            @csrf
                                                            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis komentar untuk subtask ini..." required></textarea>
                                                            <div class="d-flex justify-content-end gap-2">
                                                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#subtaskCommentForm{{ $subtask->subtask_id }}">Batal</button>
                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    <i class="bi bi-send"></i> Kirim
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="collapse" id="subtaskComments{{ $subtask->subtask_id }}">
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
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Belum ada subtask.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        @foreach($card->subtasks as $subtask)
            <div class="modal fade" id="rejectSubtaskModal{{ $subtask->subtask_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('subtasks.reject', $subtask->subtask_id) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Subtask: {{ $subtask->subtask_title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Alasan</label>
                                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection
