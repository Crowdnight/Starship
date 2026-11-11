@extends('layouts.app')

@section('title', 'Catatan Publik')
@section('page-heading', 'Jelajahi Catatan')

@push('styles')
<style>
    .public-page {
        max-width: 1280px;
        margin: 0 auto;
    }

    .public-intro {
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
        padding: clamp(24px, 4vw, 42px);
        border: 1px solid var(--border);
        border-radius: 24px;
        background:
            radial-gradient(circle at 90% 15%, color-mix(in srgb, var(--primary) 16%, transparent), transparent 34%),
            var(--white);
        box-shadow: var(--shadow);
    }

    .public-intro-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    .public-intro h1 {
        max-width: 620px;
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.15;
        letter-spacing: -1.2px;
    }

    .public-intro p {
        max-width: 560px;
        margin-top: 12px;
        color: var(--muted);
        font-size: 15px;
        line-height: 1.7;
    }

    .public-feed-heading {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 17px;
    }

    .public-feed-heading h2 {
        font-size: 19px;
        letter-spacing: -.3px;
    }

    .public-feed-count {
        color: var(--muted);
        font-size: 13px;
    }

    .public-notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 290px), 1fr));
        gap: 20px;
    }

    .public-note-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        padding: 22px;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: var(--white);
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        animation: publicAppear .4s ease both;
    }

    .public-note-card.note-color-surface {
        background: var(--note-background);
        color: var(--note-foreground);
        border-color: color-mix(in srgb, var(--note-foreground) 15%, transparent);
    }

    .public-note-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 34px color-mix(in srgb, var(--text) 12%, transparent);
    }

    .public-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 16px;
    }

    .public-category {
        display: inline-flex;
        max-width: 65%;
        align-items: center;
        overflow: hidden;
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .public-date {
        flex-shrink: 0;
        color: var(--muted);
        font-size: 11px;
    }

    .public-date-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }

    .public-view-count {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--muted);
        font-size: 11px;
        white-space: nowrap;
    }

    .public-card-stats {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0 0 18px;
        color: var(--muted);
        font-size: 11px;
    }

    .public-card-stats span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .public-card-reactions {
        display: inline-flex;
        gap: 12px;
        margin-left: auto;
    }

    .public-note-card.note-color-surface .public-category {
        background: color-mix(in srgb, var(--white) 78%, transparent);
        color: var(--text);
    }

    .public-note-card.note-color-surface .public-date,
    .public-note-card.note-color-surface .public-author-name {
        color: var(--note-foreground);
        opacity: .78;
    }

    .public-note-title {
        margin-bottom: 9px;
        font-size: 20px;
        line-height: 1.35;
        letter-spacing: -.4px;
        overflow-wrap: anywhere;
    }

    .public-note-title a {
        color: inherit;
        text-decoration: none;
    }

    .public-note-title a:hover {
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .public-note-excerpt {
        display: -webkit-box;
        overflow: hidden;
        margin-bottom: 20px;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.75;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }

    .public-note-card.note-color-surface .public-note-excerpt {
        color: var(--note-foreground);
        opacity: .82;
    }

    .public-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid var(--border);
    }

    .public-note-card.note-color-surface .public-card-footer {
        border-color: color-mix(in srgb, var(--note-foreground) 18%, transparent);
    }

    .public-author {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 9px;
    }

    .public-author-avatar {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        font-size: 12px;
    }

    .public-author-name {
        overflow: hidden;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .public-read-more {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 5px;
        color: var(--primary-dark);
        font-size: 12px;
        font-weight: 800;
    }

    .public-note-card.note-color-surface .public-read-more {
        color: var(--note-foreground);
    }

    .public-read-more:hover {
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .public-empty {
        padding: 42px 24px;
        border: 1px dashed var(--border);
        border-radius: 20px;
        background: var(--white);
        text-align: center;
    }

    .public-empty h2 {
        margin-bottom: 8px;
        font-size: 20px;
    }

    .public-empty p {
        color: var(--muted);
        line-height: 1.6;
    }

    .public-pagination {
        margin-top: 26px;
    }

    @keyframes publicAppear {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 600px) {
        .public-intro {
            margin-bottom: 24px;
            border-radius: 18px;
        }

        .public-note-card {
            padding: 19px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .public-note-card {
            animation: none;
            transition: none;
        }
    }
</style>
@endpush

@section('content')
<div class="public-page">
    <header class="public-intro">
        <div class="public-intro-label">🌍 Ruang berbagi {{ config('app.name') }}</div>
        <h1>Temukan cerita dari berbagai sudut pandang.</h1>
        <p>Jelajahi catatan yang dibagikan oleh komunitas, baca ceritanya, dan ikut berdiskusi dengan kreatornya.</p>
    </header>

    @if($notes->isEmpty())
        <section class="public-empty">
            <h2>Belum ada catatan publik</h2>
            <p>Belum ada cerita yang dibagikan. Silakan kembali lagi nanti.</p>
        </section>
    @else
        <div class="public-feed-heading">
            <h2>Catatan terbaru</h2>
            <span class="public-feed-count">{{ $notes->total() }} catatan</span>
        </div>

        <div class="public-notes-grid">
            @foreach($notes as $note)
                <article
                    class="public-note-card {{ $note->note_color ? 'note-color-surface' : '' }}"
                    style="animation-delay: {{ $loop->index * 0.05 }}s; @if($note->note_color)--note-background: {{ $note->note_color }}; --note-foreground: {{ $note->foreground_color }};@endif"
                >
                    <div class="public-card-meta">
                        <span class="public-category">{{ $note->category }}</span>
                        <div class="public-date-group">
                            <time class="public-date" datetime="{{ $note->created_at->toIso8601String() }}">
                                {{ $note->created_at->format('d M Y') }}
                            </time>
                            @if($note->wasEdited())
                                <span class="edited-indicator">Edited</span>
                            @endif
                        </div>
                    </div>

                    <h3 class="public-note-title">
                        <a href="{{ route('public-notes.show', $note) }}">{{ $note->title }}</a>
                    </h3>

                    <p class="public-note-excerpt">{{ \Illuminate\Support\Str::limit($note->content, 220) }}</p>

                    <div class="public-card-stats" data-note-stats>
                        <span aria-label="{{ $note->public_views_count }} views publik">
                            <span aria-hidden="true">◉</span> {{ $note->public_views_count }} views
                        </span>
                        <span aria-label="{{ $note->comments_count }} komentar">
                            <span aria-hidden="true">▤</span> {{ $note->comments_count }} komentar
                        </span>
                        <span class="public-card-reactions">
                            <span aria-label="{{ $note->likes_count }} suka"><span aria-hidden="true">👍</span> {{ $note->likes_count }}</span>
                            <span aria-label="{{ $note->dislikes_count }} tidak suka"><span aria-hidden="true">👎</span> {{ $note->dislikes_count }}</span>
                        </span>
                    </div>

                    <div class="public-card-footer">
                        <div class="public-author">
                            @include('users._avatar', [
                                'user' => $note->user,
                                'class' => 'public-author-avatar',
                            ])
                            <span class="public-author-name">{{ $note->user?->name ?? 'Pengguna' }}</span>
                        </div>

                        <a
                            href="{{ route('public-notes.show', $note) }}"
                            class="public-read-more"
                            aria-label="Baca catatan {{ $note->title }}"
                        >
                            Baca catatan <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="public-pagination">
            {{ $notes->links() }}
        </div>
    @endif
</div>
@endsection
