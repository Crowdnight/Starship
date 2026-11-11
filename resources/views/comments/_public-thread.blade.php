<article class="comment-item" data-delete-item>
    @include('users._avatar', [
        'user' => $comment->user,
        'class' => 'comment-avatar',
    ])

    <div class="comment-info">
        <div class="comment-header">
            <span class="comment-name">{{ $comment->user?->name ?? 'Pengguna' }}</span>
            @if((int) $comment->user_id === (int) $note->user_id)
                <span class="creator-badge">Kreator</span>
            @endif
        </div>
        <div class="comment-content">{{ $comment->content }}</div>
        <time class="comment-date" datetime="{{ $comment->created_at->toIso8601String() }}">
            {{ $comment->created_at->format('d M Y, H:i') }}
            @if($comment->wasEdited())
                <span class="edited-indicator">Edited</span>
            @endif
        </time>

        @auth
            <button
                type="button"
                class="comment-reply-action"
                data-comment-reply="{{ $comment->id }}"
                aria-expanded="false"
            ><span aria-hidden="true">↩</span> Balas</button>
            <form
                action="{{ route('comments.store', $note) }}"
                method="POST"
                class="comment-reply-form"
                data-comment-reply-form="{{ $comment->id }}"
                hidden
            >
                @csrf
                <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
                <textarea
                    name="content"
                    maxlength="2000"
                    required
                    aria-label="Balas komentar {{ $comment->user?->name ?? 'Pengguna' }}"
                    placeholder="Tulis balasan..."
                >{{ old('content') }}</textarea>
                @error('content')
                    <small class="comment-error">{{ $message }}</small>
                @enderror
                <div class="comment-edit-actions">
                    <button type="button" class="comment-edit-cancel" data-comment-reply-cancel="{{ $comment->id }}">Batal</button>
                    <button type="submit" class="comment-edit-save">Kirim balasan</button>
                </div>
            </form>

            @if((int) $comment->user_id === (int) auth()->id())
                <form
                    action="{{ route('comments.update', $comment) }}"
                    method="POST"
                    class="comment-edit-form"
                    data-comment-edit-form="{{ $comment->id }}"
                    hidden
                >
                    @csrf
                    @method('PUT')
                    <textarea name="content" maxlength="2000" required aria-label="Edit komentar">{{ $comment->content }}</textarea>
                    <input type="hidden" name="redirect_to" value="public-notes.show">
                    <div class="comment-edit-actions">
                        <button type="button" class="comment-edit-cancel" data-comment-cancel="{{ $comment->id }}">Batal</button>
                        <button type="submit" class="comment-edit-save">Simpan</button>
                    </div>
                </form>
            @endif
        @endauth
    </div>

    @auth
        @if((int) $comment->user_id === (int) auth()->id())
        <div class="comment-actions">
            <details class="comment-menu-wrapper">
                <summary class="comment-menu-trigger" aria-label="Opsi komentar">…</summary>
                <div class="comment-menu">
                    <button type="button" data-comment-edit="{{ $comment->id }}">Edit komentar</button>
                    <form
                        action="{{ route('comments.destroy', $comment) }}"
                        method="POST"
                        data-confirm-delete
                        data-delete-label="komentar"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="public-notes.show">
                        <button class="comment-delete-action" type="submit">Hapus komentar</button>
                    </form>
                </div>
            </details>
        </div>
        @endif
    @endauth

    @if($comment->replies->isNotEmpty())
        <div class="comment-replies">
            @foreach($comment->replies as $reply)
                @include('comments._public-thread', ['comment' => $reply, 'note' => $note])
            @endforeach
        </div>
    @endif
</article>
