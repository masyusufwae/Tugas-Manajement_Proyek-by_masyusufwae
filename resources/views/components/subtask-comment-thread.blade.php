@php
    $depth = $depth ?? 0;
    $indentClass = $depth > 0 ? 'ms-' . min($depth * 3, 5) : '';
    $replyCollapseId = 'replySubtaskComment'.$comment->comment_id;
@endphp

<div class="subtask-comments {{ $indentClass }}">
    <div class="d-flex justify-content-between flex-wrap align-items-start gap-2">
        <div>
            <strong>{{ $comment->user->full_name ?? $comment->user->username }}</strong>
            <small class="text-muted d-block">{{ $comment->created_at?->format('d M Y H:i') }}</small>
        </div>
    </div>
    <p class="mt-2 mb-2">{{ $comment->comment }}</p>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $replyCollapseId }}">
            <i class="bi bi-chat-left-text"></i> Balas
        </button>
        @if(auth()->id() === $comment->user_id)
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editSubtaskComment{{ $comment->comment_id }}">
                Edit
            </button>
            <form action="{{ route('comments.subtask.delete', $comment->comment_id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
            </form>
        @endif
    </div>

    @if(auth()->id() === $comment->user_id)
        <div class="collapse mt-3" id="editSubtaskComment{{ $comment->comment_id }}">
            <form action="{{ route('comments.subtask.update', $comment->comment_id) }}" method="POST" class="card card-body border-0 shadow-sm">
                @csrf
                @method('PUT')
                <textarea name="comment" class="form-control mb-2" rows="2" required>{{ old('comment', $comment->comment) }}</textarea>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#editSubtaskComment{{ $comment->comment_id }}">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    <div class="collapse mt-3" id="{{ $replyCollapseId }}">
        <form action="{{ route('comments.subtask.store', $subtask->subtask_id) }}" method="POST" class="card card-body border-0 shadow-sm">
            @csrf
            <input type="hidden" name="parent_comment_id" value="{{ $comment->comment_id }}">
            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis balasan..." required></textarea>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#{{ $replyCollapseId }}">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Kirim</button>
            </div>
        </form>
    </div>

    @if($comment->replies && $comment->replies->isNotEmpty())
        <div class="mt-3">
            @foreach($comment->replies as $child)
                @include('components.subtask-comment-thread', [
                    'comment' => $child,
                    'subtask' => $subtask,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
