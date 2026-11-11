@extends('layouts.app')

@section('title', 'Detail Catatan')
@section('page-heading', 'Detail Catatan')

@section('content')

<style>
    .note-discussion {
        margin-top: 22px;
        padding: 24px;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: var(--white);
        box-shadow: var(--shadow);
    }

    .note-discussion-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .note-discussion-heading h2 {
        font-size: 18px;
    }

    .note-discussion-count {
        display: inline-grid;
        min-width: 30px;
        height: 30px;
        place-items: center;
        padding: 0 8px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 12px;
        font-weight: 800;
    }

    .note-comment-list {
        display: grid;
        gap: 16px;
    }

    .note-comment {
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .note-comment-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .note-comment-avatar {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border-radius: 50%;
        font-size: 10px;
    }

    .note-comment-author {
        font-size: 13px;
        font-weight: 800;
    }

    .note-comment-creator {
        padding: 4px 8px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 10px;
        font-weight: 800;
    }

    .note-comment-content {
        color: var(--text);
        font-size: 13px;
        line-height: 1.65;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .note-comment time {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: 11px;
    }

    .note-comment-replies {
        display: grid;
        gap: 12px;
        margin: 14px 0 0 12px;
        padding-left: 16px;
        border-left: 2px solid var(--border);
    }

    .note-comment-reply {
        padding: 12px;
        border-radius: 12px;
        background: var(--input-surface);
    }

    .note-reply-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--white);
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .note-reply-button:hover,
    .note-reply-button:focus-visible {
        border-color: var(--primary);
        background: var(--primary-light);
        outline: none;
    }

    .note-reply-form {
        display: grid;
        gap: 9px;
        margin-top: 10px;
    }

    .note-reply-form[hidden] {
        display: none;
    }

    .note-reply-form textarea {
        width: 100%;
        min-height: 78px;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--input-surface);
        color: var(--text);
        font: inherit;
        font-size: 13px;
        resize: vertical;
    }

    .note-reply-form button {
        padding: 8px 12px;
        border: 0;
        border-radius: 9px;
        background: var(--primary);
        color: var(--on-primary);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .note-reply-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .note-reply-actions .note-reply-cancel {
        background: var(--input-surface);
        color: var(--muted);
    }

    .note-discussion-empty {
        padding: 18px 0 4px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 13px;
    }
</style>

<div class="detail-container">

    <a href="{{ route('notes.index') }}" class="back-link">
        ← Kembali ke Dashboard
    </a>

    <article
        class="detail-card {{ $note->note_color ? 'note-color-surface' : '' }}"
        data-delete-item
        @if($note->note_color)
            style="--note-background: {{ $note->note_color }}; --note-foreground: {{ $note->foreground_color }};"
        @endif
    >

        <div class="detail-meta">
            <span class="category-badge">{{ $note->category }}</span>

            <span>
                Dibuat {{ $note->created_at->format('d F Y, H:i') }}
                @if($note->wasEdited())
                    <span class="edited-indicator">Edited</span>
                @endif
            </span>
        </div>

        <h1>{{ $note->title }}</h1>

        <div class="detail-divider"></div>

        <div class="detail-content">{{ $note->content }}</div>

        <div class="detail-divider"></div>

        <div class="detail-footer">
            <span>
                Terakhir diperbarui:
                {{ $note->updated_at->format('d F Y, H:i') }}
            </span>

            <div class="form-actions">
                <a href="{{ route('notes.edit', $note) }}" class="button button-primary">
                    ✎ Edit
                </a>

                <form
                    action="{{ route('notes.destroy', $note) }}"
                    method="POST"
                    data-confirm-delete
                    data-delete-label="catatan"
                >
                    @csrf
                    @method('DELETE')

                    <button class="button button-danger" type="submit">
                        Hapus
                    </button>
                </form>
            </div>
        </div>

    </article>

    <section class="note-discussion" aria-labelledby="note-discussion-title">
        <div class="note-discussion-heading">
            <h2 id="note-discussion-title">Komentar</h2>
            <span class="note-discussion-count" aria-label="{{ $note->comments->count() }} komentar">
                {{ $note->comments->count() }}
            </span>
        </div>

        @if($note->comments->isEmpty())
            <p class="note-discussion-empty">Belum ada komentar pada catatan ini.</p>
        @else
            <div class="note-comment-list">
                @foreach($note->rootComments as $comment)
                    <article class="note-comment">
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
                @endforeach
            </div>
        @endif
    </section>

</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-note-reply]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector(`[data-note-reply-form="${button.dataset.noteReply}"]`);
            form.hidden = !form.hidden;
            button.setAttribute('aria-expanded', String(!form.hidden));
            if (!form.hidden) form.querySelector('textarea').focus();
        });
    });

    document.querySelectorAll('[data-note-reply-cancel]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector(`[data-note-reply-form="${button.dataset.noteReplyCancel}"]`);
            form.hidden = true;
            document.querySelector(`[data-note-reply="${button.dataset.noteReplyCancel}"]`)
                .setAttribute('aria-expanded', 'false');
        });
    });
</script>
@endpush