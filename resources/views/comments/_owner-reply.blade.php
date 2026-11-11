<article class="note-comment-reply">
    <div class="note-comment-header">
        @include('users._avatar', [
            'user' => $comment->user,
            'class' => 'note-comment-avatar',
        ])
        <span class="note-comment-author">{{ $comment->user?->name ?? 'Pengguna' }}</span>
        @if((int) $comment->user_id === (int) $note->user_id)
            <span class="note-comment-creator">Kreator</span>
        @endif
    </div>
    <p class="note-comment-content">{{ $comment->content }}</p>
    <time datetime="{{ $comment->created_at->toIso8601String() }}">
        {{ $comment->created_at->format('d M Y, H:i') }}
        @if($comment->wasEdited())
            <span class="edited-indicator">Edited</span>
        @endif
    </time>

    @if($note->is_public)
        <button
            type="button"
            class="note-reply-button"
            data-note-reply="{{ $comment->id }}"
            aria-expanded="false"
        ><span aria-hidden="true">↩</span> Balas</button>
        <form
            action="{{ route('comments.store', $note) }}"
            method="POST"
            class="note-reply-form"
            data-note-reply-form="{{ $comment->id }}"
            hidden
        >
            @csrf
            <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
            <input type="hidden" name="redirect_to" value="notes.show">
            <textarea name="content" maxlength="2000" required aria-label="Balas komentar {{ $comment->user?->name ?? 'Pengguna' }}" placeholder="Tulis balasan...">{{ old('content') }}</textarea>
            @error('content')
                <small class="comment-error">{{ $message }}</small>
            @enderror
            <div class="note-reply-actions">
                <button type="button" class="note-reply-cancel" data-note-reply-cancel="{{ $comment->id }}">Batal</button>
                <button type="submit">Kirim balasan</button>
            </div>
        </form>
    @endif

    @if($comment->replies->isNotEmpty())
        <div class="note-comment-replies">
            @foreach($comment->replies as $reply)
                @include('comments._owner-reply', ['comment' => $reply, 'note' => $note])
            @endforeach
        </div>
    @endif
</article>
