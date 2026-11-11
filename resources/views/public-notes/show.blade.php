@extends('layouts.app')

@section('title', $note->title)
@section('page-heading', 'Catatan Publik')

@push('styles')
<style>
    .public-note-detail {
        max-width: 1180px;
        margin: 0 auto;
    }

    .back-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        transition: color .2s ease;
    }

    .back-note:hover {
        color: var(--primary-dark);
    }

    .creator-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
        align-items: start;
        gap: 22px;
    }

    .creator-note,
    .creator-interaction {
        min-width: 0;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--white);
        box-shadow: var(--shadow);
    }

    .creator-note {
        display: flex;
        min-height: 360px;
        flex-direction: column;
        padding: clamp(24px, 4vw, 38px);
        overflow-wrap: anywhere;
    }

    .creator-note.note-color-surface {
        background: var(--note-background);
        color: var(--note-foreground);
        border-color: color-mix(in srgb, var(--note-foreground) 15%, transparent);
    }

    .note-category {
        display: inline-flex;
        max-width: 100%;
        align-self: flex-start;
        overflow: hidden;
        padding: 7px 12px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .creator-note.note-color-surface .note-category {
        background: color-mix(in srgb, var(--white) 78%, transparent);
        color: var(--text);
    }

    .creator-note h1 {
        margin: 22px 0 16px;
        font-size: clamp(27px, 4vw, 38px);
        line-height: 1.2;
        letter-spacing: -1px;
    }

    .note-content {
        color: var(--text);
        font-size: 16px;
        line-height: 1.9;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .creator-note.note-color-surface .note-content {
        color: var(--note-foreground);
    }

    .note-author {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: auto;
        padding-top: 30px;
    }

    .creator-avatar {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        border-radius: 50%;
        font-size: 15px;
    }

    .note-author-name {
        font-size: 13px;
        font-weight: 800;
    }

    .note-author-role,
    .creator-date {
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .note-view-count {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
    }

    .note-reactions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 24px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--input-surface);
    }

    .note-reaction-form {
        margin: 0;
    }

    .note-reaction-button {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--white);
        color: var(--text);
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .note-reaction-button:hover,
    .note-reaction-button:focus-visible {
        transform: translateY(-1px);
        border-color: var(--primary);
        outline: none;
    }

    .note-reaction-button.is-selected {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary-dark);
    }

    .note-reaction-login {
        margin-left: auto;
        color: var(--muted);
        font-size: 11px;
    }

    .creator-note.note-color-surface .note-reactions {
        border-color: color-mix(in srgb, var(--note-foreground) 18%, transparent);
        background: color-mix(in srgb, var(--white) 35%, transparent);
    }

    .creator-note.note-color-surface .note-author-role,
    .creator-note.note-color-surface .creator-date {
        color: var(--note-foreground);
        opacity: .76;
    }

    .creator-note.note-color-surface .note-view-count {
        color: var(--note-foreground);
        opacity: .76;
    }

    .creator-note.note-color-surface .edited-indicator {
        opacity: 1;
    }

    .creator-interaction {
        padding: 24px;
    }

    .comments-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .comments-title {
        font-size: 18px;
        letter-spacing: -.3px;
    }

    .comments-count {
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

    .comment-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--input-surface);
    }

    .comment-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 4px;
        border: 0;
        outline: 0;
        resize: vertical;
        background: transparent;
        color: var(--text);
        font-family: inherit;
        font-size: 14px;
        line-height: 1.6;
    }

    .comment-form textarea::placeholder {
        color: var(--muted);
    }

    .comment-form button {
        align-self: flex-end;
        cursor: pointer;
    }

    .comment-error {
        color: var(--danger);
        font-size: 12px;
    }

    .login-comment {
        margin-bottom: 20px;
        padding: 16px;
        border: 1px solid color-mix(in srgb, var(--primary) 22%, var(--border));
        border-radius: 16px;
        background: color-mix(in srgb, var(--primary-light) 55%, var(--white));
    }

    .login-comment p {
        margin-bottom: 12px;
        color: var(--text);
        font-size: 13px;
        line-height: 1.6;
    }

    .comment-list {
        padding-top: 18px;
        border-top: 1px solid var(--border);
    }

    .comment-item {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        align-items: start;
        gap: 11px;
        margin-bottom: 20px;
        animation: commentAppear .3s ease both;
    }

    .comment-avatar {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 50%;
        font-size: 12px;
    }

    .creator-interaction .comment-list .comment-item .comment-info {
        display: flex !important;
        min-width: 0;
        flex-direction: column !important;
        align-items: stretch !important;
        text-align: left !important;
    }

    .creator-interaction .comment-list .comment-item .comment-info .comment-header {
        display: flex;
        width: auto !important;
        min-width: 0;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .creator-interaction .comment-list .comment-item .comment-info .comment-name {
        display: inline-block !important;
        width: auto !important;
        flex: 0 1 auto;
        color: var(--text);
        font-size: 13px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .creator-badge {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        width: auto !important;
        padding: 4px 8px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 10px;
        font-weight: 800;
    }

    .creator-interaction .comment-list .comment-item .comment-info .comment-content {
        display: block !important;
        width: 100% !important;
        margin: 5px 0 0 !important;
        padding: 0 !important;
        color: var(--text);
        line-height: 1.65;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .creator-interaction .comment-list .comment-item .comment-info .comment-date {
        display: block !important;
        width: 100% !important;
        margin: 7px 0 0 !important;
        color: var(--muted);
        font-size: 11px;
    }

    .creator-interaction .comment-list .comment-item .comment-info .edited-indicator {
        margin-left: 6px;
        vertical-align: middle;
    }

    .comment-actions {
        position: relative;
        grid-column: 3;
        grid-row: 1;
    }

    .comment-menu {
        position: absolute;
        top: 34px;
        right: 0;
        z-index: 2;
        display: grid;
        min-width: 132px;
        gap: 4px;
        padding: 6px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--white);
        box-shadow: var(--shadow);
    }

    .comment-menu button {
        width: 100%;
        padding: 8px 10px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--text);
        font-size: 12px;
        text-align: left;
        cursor: pointer;
    }

    .comment-reply-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 9px;
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 999px;
        background: transparent;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .comment-reply-action span {
        color: var(--primary-dark);
        font-size: 15px;
    }

    .comment-reply-action:hover,
    .comment-reply-action:focus-visible {
        border-color: var(--border);
        background: var(--input-surface);
        color: var(--primary-dark);
        outline: none;
    }

    .comment-menu button:hover,
    .comment-menu button:focus-visible {
        background: var(--primary-light);
        outline: none;
    }

    .comment-menu .comment-delete-action {
        color: var(--danger);
    }

    .comment-menu .comment-delete-action:hover {
        background: color-mix(in srgb, var(--danger) 10%, var(--white));
    }

    .comment-menu-trigger {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        list-style: none;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--white);
        color: var(--muted);
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
    }

    .comment-menu-trigger::-webkit-details-marker {
        display: none;
    }

    .comment-menu-trigger:hover,
    .comment-menu-trigger:focus-visible {
        border-color: var(--primary);
        color: var(--primary-dark);
        outline: none;
    }

    .comment-edit-form {
        display: grid;
        gap: 9px;
        width: 100%;
        margin-top: 10px;
    }

    .comment-edit-form[hidden] {
        display: none;
    }

    .comment-edit-form textarea {
        width: 100%;
        min-height: 82px;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--input-surface);
        color: var(--text);
        font: inherit;
        font-size: 13px;
        line-height: 1.6;
        resize: vertical;
    }

    .comment-reply-form {
        display: grid;
        gap: 9px;
        width: 100%;
        margin-top: 10px;
    }

    .comment-reply-form[hidden] {
        display: none;
    }

    .comment-reply-action[aria-expanded="true"] {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary-dark);
    }

    .comment-reply-form textarea {
        width: 100%;
        min-height: 78px;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--input-surface);
        color: var(--text);
        font: inherit;
        font-size: 13px;
        line-height: 1.6;
        resize: vertical;
    }

    .comment-reply-form button {
        justify-self: end;
        padding: 7px 10px;
        border: 0;
        border-radius: 8px;
        background: var(--primary);
        color: var(--on-primary);
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .comment-replies {
        display: grid;
        grid-column: 2 / -1;
        gap: 12px;
        margin-top: 12px;
        padding-left: 14px;
        border-left: 2px solid var(--border);
    }

    .comment-replies > .comment-item {
        padding: 11px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--input-surface);
    }

    .comment-replies .comment-avatar {
        width: 32px;
        height: 32px;
        font-size: 10px;
    }

    .comment-replies .comment-replies {
        grid-column: 1 / -1;
        margin-left: 4px;
        padding-left: 10px;
    }

    .comment-reply-form {
        padding: 11px;
        border: 1px solid var(--border);
        border-radius: 13px;
        background: var(--input-surface);
    }

    .comment-reply-form textarea:focus-visible {
        border-color: var(--primary);
        outline: 3px solid color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .comment-edit-actions button:hover,
    .comment-edit-actions button:focus-visible {
        filter: brightness(.96);
        outline: 2px solid color-mix(in srgb, var(--primary) 28%, transparent);
        outline-offset: 2px;
    }

    .comment-edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .comment-edit-actions button {
        padding: 7px 10px;
        border: 0;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .comment-edit-cancel {
        background: var(--input-surface);
        color: var(--muted);
    }

    .comment-edit-save {
        background: var(--primary);
        color: var(--on-primary);
    }

    .comment-empty {
        padding: 22px 12px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
        text-align: center;
    }

    .comment-empty span {
        display: block;
        margin-bottom: 5px;
        font-size: 22px;
    }

    @keyframes commentAppear {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 900px) {
        .creator-layout {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .creator-interaction {
            padding: 20px;
        }
    }

    @media (max-width: 720px) {
        .creator-layout {
            grid-template-columns: 1fr;
        }

        .creator-note {
            min-height: 280px;
        }
    }

    @media (max-width: 420px) {
        .creator-interaction {
            padding: 17px;
        }

        .comment-item {
            grid-template-columns: 34px minmax(0, 1fr) auto;
            gap: 9px;
        }

        .comment-avatar {
            width: 34px;
            height: 34px;
            font-size: 11px;
        }

    }

    @media (prefers-reduced-motion: reduce) {
        .back-note,
        .comment-item {
            transition: none;
            animation: none;
        }
    }
</style>
@endpush

@section('content')
<main class="public-note-detail">
    <a href="{{ route('public-notes.index') }}" class="back-note">
        <span aria-hidden="true">←</span> Kembali ke Catatan Publik
    </a>

    <div class="creator-layout">
        <article
            class="creator-note {{ $note->note_color ? 'note-color-surface' : '' }}"
            @if($note->note_color)
                style="--note-background: {{ $note->note_color }}; --note-foreground: {{ $note->foreground_color }};"
            @endif
        >
            <span class="note-category">{{ $note->category }}</span>
            <h1>{{ $note->title }}</h1>
            <div class="note-content">{{ $note->content }}</div>

            <div class="note-reactions" aria-label="Reaksi catatan">
                @auth
                    @if((int) auth()->id() !== (int) $note->user_id)
                        <form action="{{ route('note-reactions.store', $note) }}" method="POST" class="note-reaction-form">
                            @csrf
                            <input type="hidden" name="reaction" value="like">
                            <button
                                type="submit"
                                class="note-reaction-button {{ $userReaction === 'like' ? 'is-selected' : '' }}"
                                aria-pressed="{{ $userReaction === 'like' ? 'true' : 'false' }}"
                                aria-label="Suka catatan, {{ $note->likes_count }} suka"
                            >
                                <span aria-hidden="true">👍</span>
                                <span>{{ $note->likes_count }}</span>
                            </button>
                        </form>
                        <form action="{{ route('note-reactions.store', $note) }}" method="POST" class="note-reaction-form">
                            @csrf
                            <input type="hidden" name="reaction" value="dislike">
                            <button
                                type="submit"
                                class="note-reaction-button {{ $userReaction === 'dislike' ? 'is-selected' : '' }}"
                                aria-pressed="{{ $userReaction === 'dislike' ? 'true' : 'false' }}"
                                aria-label="Tidak suka catatan, {{ $note->dislikes_count }} tidak suka"
                            >
                                <span aria-hidden="true">👎</span>
                                <span>{{ $note->dislikes_count }}</span>
                            </button>
                        </form>
                    @else
                        <span class="note-reaction-button" aria-label="{{ $note->likes_count }} suka">
                            <span aria-hidden="true">👍</span> {{ $note->likes_count }}
                        </span>
                        <span class="note-reaction-button" aria-label="{{ $note->dislikes_count }} tidak suka">
                            <span aria-hidden="true">👎</span> {{ $note->dislikes_count }}
                        </span>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="note-reaction-button" aria-label="{{ $note->likes_count }} suka">
                        <span aria-hidden="true">👍</span> {{ $note->likes_count }}
                    </a>
                    <a href="{{ route('login') }}" class="note-reaction-button" aria-label="{{ $note->dislikes_count }} tidak suka">
                        <span aria-hidden="true">👎</span> {{ $note->dislikes_count }}
                    </a>
                    <span class="note-reaction-login">Login untuk memberi reaksi</span>
                @endauth
            </div>

            <div class="note-author">
                @include('users._avatar', [
                    'user' => $note->user,
                    'class' => 'creator-avatar',
                ])
                <div>
                    <div class="note-author-name">{{ $note->user?->name ?? 'Pengguna' }}</div>
                    <div class="note-author-role">Kreator catatan</div>
                    <time class="creator-date" datetime="{{ $note->created_at->toIso8601String() }}">
                        {{ $note->created_at->format('d M Y, H:i') }}
                        @if($note->wasEdited())
                            <span class="edited-indicator">Edited</span>
                        @endif
                    </time>
                    <div class="note-view-count" aria-label="{{ $note->public_views_count }} pembaca">
                        <span aria-hidden="true">◉</span>
                        {{ $note->public_views_count }} pembaca
                    </div>
                </div>
            </div>
        </article>

        <section class="creator-interaction" aria-labelledby="comments-title">
            <div class="comments-heading">
                <h2 class="comments-title" id="comments-title">Diskusi</h2>
                <span class="comments-count" aria-label="{{ $note->comments->count() }} komentar">
                    {{ $note->comments->count() }}
                </span>
            </div>

            @auth
                <form action="{{ route('comments.store', $note) }}" method="POST" class="comment-form">
                    @csrf
                    <textarea
                        name="content"
                        placeholder="Bagikan tanggapanmu dengan sopan..."
                        maxlength="2000"
                        required
                        aria-label="Tulis komentar"
                    >{{ old('content') }}</textarea>

                    @error('content')
                        <small class="comment-error">{{ $message }}</small>
                    @enderror

                    <button type="submit" class="button button-primary">Kirim komentar <span aria-hidden="true">→</span></button>
                </form>
            @else
                <div class="login-comment">
                    <p>Masuk untuk ikut berdiskusi dengan kreator dan komunitas.</p>
                    <a href="{{ route('login') }}" class="button button-primary">Login untuk berkomentar</a>
                </div>
            @endauth

            <div class="comment-list">
                @forelse($note->rootComments as $comment)
                    @include('comments._public-thread', ['comment' => $comment, 'note' => $note])
                @empty
                    <div class="comment-empty">
                        <span aria-hidden="true">💬</span>
                        Belum ada komentar. Jadilah yang pertama memulai percakapan!
                    </div>
                @endforelse
            </div>
        </section>
    </div>

</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-comment-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const commentId = button.dataset.commentEdit;
            const editForm = document.querySelector(`[data-comment-edit-form="${commentId}"]`);
            const menu = button.closest('details');
            const comment = button.closest('.comment-item');
            editForm.hidden = false;
            comment.querySelector('.comment-content').hidden = true;
            comment.querySelector('.comment-date').hidden = true;
            menu.open = false;
            editForm.querySelector('textarea').focus();
        });
    });

    document.querySelectorAll('[data-comment-cancel]').forEach((button) => {
        button.addEventListener('click', () => {
            const editForm = document.querySelector(`[data-comment-edit-form="${button.dataset.commentCancel}"]`);
            const comment = button.closest('.comment-item');
            editForm.hidden = true;
            comment.querySelector('.comment-content').hidden = false;
            comment.querySelector('.comment-date').hidden = false;
        });
    });

    document.querySelectorAll('[data-comment-reply]').forEach((button) => {
        button.addEventListener('click', () => {
            const commentId = button.dataset.commentReply;
            const form = document.querySelector(`[data-comment-reply-form="${commentId}"]`);
            form.hidden = !form.hidden;
            button.setAttribute('aria-expanded', String(!form.hidden));
            if (!form.hidden) {
                form.querySelector('textarea').focus();
            }
        });
    });

    document.querySelectorAll('[data-comment-reply-cancel]').forEach((button) => {
        button.addEventListener('click', () => {
            const commentId = button.dataset.commentReplyCancel;
            const form = document.querySelector(`[data-comment-reply-form="${commentId}"]`);
            form.hidden = true;
            document.querySelector(`[data-comment-reply="${commentId}"]`)
                .setAttribute('aria-expanded', 'false');
        });
    });

</script>
@endpush
