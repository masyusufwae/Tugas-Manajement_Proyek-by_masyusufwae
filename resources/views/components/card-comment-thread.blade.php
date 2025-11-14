@php
    $depth = $depth ?? 0;
    $indentClass = $depth > 0 ? 'ms-' . min($depth * 3, 5) : '';
    $replyCollapseId = 'replyCardComment'.$comment->comment_id;
@endphp

<div class="comment-box {{ $indentClass }}">
    <div class="d-flex justify-content-between flex-wrap align-items-start gap-2">
        <div>
            <strong>{{ $comment->user->full_name ?? $comment->user->username }}</strong>
            <small class="text-muted d-block">{{ $comment->created_at?->format('d M Y H:i') }}</small>
        </div>
    </div>
    <p class="mt-2 mb-2">{{ $comment->comment }}</p>
    <div class="d-flex flex-wrap gap-2">
        @if($canReply ?? false)
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $replyCollapseId }}">
                <i class="bi bi-chat-left-text"></i> Balas
            </button>
        @endif
        @if(auth()->id() === $comment->user_id)
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editCardComment{{ $comment->comment_id }}">
                Edit
            </button>
            <form action="{{ route('comments.card.delete', $comment->comment_id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
            </form>
        @endif
    </div>

    @if(auth()->id() === $comment->user_id)
        <div class="collapse mt-3" id="editCardComment{{ $comment->comment_id }}">
            <form action="{{ route('comments.card.update', $comment->comment_id) }}" method="POST" class="card card-body border-0 shadow-sm">
                @csrf
                @method('PUT')
                <textarea name="comment" class="form-control mb-2" rows="2" required>{{ old('comment', $comment->comment) }}</textarea>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#editCardComment{{ $comment->comment_id }}">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    @if($canReply ?? false)
        <div class="collapse mt-3" id="{{ $replyCollapseId }}">
            <form action="{{ route('comments.card.store', $card->card_id) }}" method="POST" class="card card-body border-0 shadow-sm">
                @csrf
                <input type="hidden" name="parent_comment_id" value="{{ $comment->comment_id }}">
                <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis balasan..." required></textarea>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#{{ $replyCollapseId }}">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Kirim</button>
                </div>
            </form>
        </div>
    @endif

    @if($comment->replies && $comment->replies->isNotEmpty())
        <div class="mt-3">
            @foreach($comment->replies as $child)
                @include('components.card-comment-thread', [
                    'comment' => $child,
                    'card' => $card,
                    'depth' => $depth + 1,
                    'canReply' => $canReply ?? false,
                ])
            @endforeach
        </div>
    @endif
</div>
