@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@push('styles')
<style>
    .dashboard-page {
        max-width: 1440px;
        margin: 0 auto;
    }

    .dashboard-welcome {
        display: flex;
        min-height: 190px;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 22px;
        padding: clamp(24px, 4vw, 40px);
        border: 1px solid color-mix(in srgb, var(--primary) 18%, var(--border));
        border-radius: 24px;
        background:
            radial-gradient(circle at 88% 18%, color-mix(in srgb, var(--primary) 21%, transparent), transparent 28%),
            linear-gradient(120deg, var(--welcome-start), var(--welcome-end));
        box-shadow: var(--shadow);
    }

    .dashboard-welcome-copy {
        position: relative;
        z-index: 1;
    }

    .dashboard-welcome .eyebrow {
        color: var(--primary-dark);
    }

    .dashboard-welcome h1 {
        margin: 12px 0 7px;
        font-size: clamp(25px, 3.5vw, 36px);
        letter-spacing: -1.1px;
    }

    .dashboard-welcome p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.65;
    }

    .dashboard-welcome-decoration {
        flex: 0 0 auto;
        color: color-mix(in srgb, var(--primary) 45%, transparent);
        font-size: clamp(70px, 10vw, 124px);
        line-height: 1;
        animation: dashboardSparkle 5s ease-in-out infinite;
    }

    .note-card-stats {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 0 0 17px;
        color: var(--muted);
        font-size: 12px;
    }

    .note-card-stats span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .welcome-clock {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 17px;
        padding: 8px 12px;
        border: 1px solid color-mix(in srgb, var(--primary) 25%, var(--border));
        border-radius: 999px;
        background: color-mix(in srgb, var(--white) 74%, transparent);
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
    }

    .welcome-clock time {
        color: var(--primary-dark);
        font-size: 14px;
        font-variant-numeric: tabular-nums;
        letter-spacing: .06em;
    }

    .dashboard-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 38px;
    }

    .dashboard-stats .stat-card {
        position: relative;
        min-height: 112px;
        overflow: hidden;
        padding: 19px;
        border-radius: 18px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .dashboard-stats .stat-card::after {
        position: absolute;
        right: -17px;
        bottom: -34px;
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--primary) 7%, transparent);
        content: "";
        pointer-events: none;
    }

    .dashboard-stats .stat-card:hover {
        transform: translateY(-3px);
        border-color: color-mix(in srgb, var(--primary) 34%, var(--border));
        box-shadow: 0 16px 34px color-mix(in srgb, var(--text) 10%, transparent);
    }

    .dashboard-stats .stat-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
        font-size: 19px;
    }

    .dashboard-stats .stat-card > div:last-child {
        min-width: 0;
    }

    .dashboard-stats .stat-card span {
        display: block;
        font-size: 11px;
        letter-spacing: .01em;
    }

    .dashboard-stats .stat-card h2 {
        margin-top: 5px;
        font-size: 27px;
        font-variant-numeric: tabular-nums;
        letter-spacing: -.7px;
    }

    .dashboard-section-heading {
        align-items: center;
    }

    .dashboard-section-heading .button {
        flex: 0 0 auto;
    }

    .dashboard-results-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 8px;
        padding: 5px 9px;
        border-radius: 999px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 800;
        vertical-align: middle;
    }

    .dashboard-search {
        max-width: 760px;
        margin-bottom: 24px;
        border-radius: 15px;
        box-shadow: var(--shadow);
    }

    .dashboard-note-grid {
        gap: 17px;
    }

    .dashboard-note-card {
        min-height: 245px;
        padding: 20px;
        border-radius: 18px;
    }

    .dashboard-note-card .note-card-top {
        margin-bottom: 16px;
    }

    .dashboard-note-card .note-title {
        font-size: 17px;
    }

    .dashboard-note-card .note-preview {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }

    .dashboard-note-card .note-card-stats {
        flex-wrap: wrap;
        padding-top: 13px;
        border-top: 1px solid var(--border);
    }

    .dashboard-note-card.note-color-surface .note-card-stats,
    .dashboard-note-card.note-color-surface .note-card-footer {
        border-color: color-mix(in srgb, var(--note-foreground) 18%, transparent);
    }

    .dashboard-note-card .note-card-footer {
        margin-top: auto;
    }

    @keyframes dashboardSparkle {
        0%, 100% { transform: rotate(-8deg) scale(.96); }
        50% { transform: rotate(8deg) scale(1.04); }
    }

    @media (max-width: 600px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .dashboard-welcome {
            min-height: 0;
            border-radius: 19px;
        }

        .dashboard-welcome-decoration {
            align-self: flex-end;
            margin-right: -6px;
            font-size: 58px;
        }

        .dashboard-section-heading {
            align-items: flex-start;
        }
    }

    @media (min-width: 601px) and (max-width: 1000px) {
        .dashboard-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-welcome-decoration {
            animation: none;
        }

        .dashboard-stats .stat-card {
            transition: none;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-page">
    <header class="dashboard-welcome">
        <div class="dashboard-welcome-copy">
            <span class="eyebrow">YOUR PERSONAL SPACE</span>
            <h1>Halo, selamat datang! 👋</h1>
            <p>Simpan ide, cerita, dan kenanganmu dalam satu tempat.</p>
            <div class="welcome-clock">
                <span aria-hidden="true">◷</span>
                <span>Waktu sekarang</span>
                <time data-realtime-clock aria-label="Jam lokal saat ini">--:--:--</time>
            </div>
        </div>
        <div class="dashboard-welcome-decoration" aria-hidden="true">✦</div>
    </header>

    <section class="stats-grid dashboard-stats" aria-label="Ringkasan aktivitas">
        <article class="stat-card">
            <div class="stat-icon purple" aria-hidden="true">▤</div>
            <div><span>Total Catatan</span><h2>{{ $totalNotes }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon pink" aria-hidden="true">♡</div>
            <div><span>Kategori</span><h2>{{ $categories->count() }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">▤</div>
            <div><span>Total Komentar</span><h2 data-total-comments>{{ $totalComments }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">◉</div>
            <div><span>Total Views</span><h2 data-total-views>{{ $totalViews }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon pink" aria-hidden="true">👍</div>
            <div><span>Total Like</span><h2 data-total-likes>{{ $totalLikes }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">👎</div>
            <div><span>Total Dislike</span><h2 data-total-dislikes>{{ $totalDislikes }}</h2></div>
        </article>
    </section>

    <section class="dashboard-notes" aria-labelledby="dashboard-notes-title">
        <div class="section-header dashboard-section-heading">
            <div>
                <h2 id="dashboard-notes-title">Catatan Saya</h2>
                <p>Kelola dan lihat aktivitas dari catatanmu.
                    <span class="dashboard-results-count">{{ $notes->total() }} catatan</span>
                </p>
            </div>
            <a href="{{ route('notes.create') }}" class="button button-primary">＋ Buat Catatan</a>
        </div>

        <form action="{{ route('notes.index') }}" method="GET" class="search-form dashboard-search">
            <span aria-hidden="true">⌕</span>
            <input
                type="search"
                name="search"
                placeholder="Cari judul, isi, atau kategori..."
                value="{{ $search }}"
                aria-label="Cari catatan"
            >
            <button type="submit" class="button button-primary">Cari</button>
            @if($search)
                <a href="{{ route('notes.index') }}" class="button button-light">Reset</a>
            @endif
        </form>

        @if($notes->count())
            <div class="notes-grid dashboard-note-grid">
                @foreach($notes as $note)
                    <article
                        class="note-card dashboard-note-card {{ $note->note_color ? 'note-color-surface' : '' }}"
                        data-delete-item
                        @if($note->note_color)
                            style="--note-background: {{ $note->note_color }}; --note-foreground: {{ $note->foreground_color }};"
                        @endif
                    >
                        <div class="note-card-top">
                            <span class="category-badge">{{ $note->category }}</span>
                            <span class="note-date">
                                {{ $note->created_at->format('d M Y') }}
                                @if($note->wasEdited())
                                    <span class="edited-indicator">Edited</span>
                                @endif
                            </span>
                        </div>

                        <a href="{{ route('notes.show', $note) }}" class="note-title">{{ $note->title }}</a>
                        <p class="note-preview">{{ \Illuminate\Support\Str::limit($note->content, 120) }}</p>

                        <div class="note-card-stats" data-note-stats>
                            <span aria-label="{{ $note->public_views_count }} views publik"><span aria-hidden="true">◉</span> {{ $note->public_views_count }} views</span>
                            <span aria-label="{{ $note->comments_count }} komentar"><span aria-hidden="true">▤</span> {{ $note->comments_count }} komentar</span>
                        </div>

                        <div class="note-card-footer">
                            <a href="{{ route('notes.show', $note) }}" class="text-link">Baca selengkapnya →</a>
                            <div class="note-actions">
                                <a href="{{ route('notes.edit', $note) }}" title="Edit" aria-label="Edit {{ $note->title }}">✎</a>
                                <form
                                    action="{{ route('notes.destroy', $note) }}"
                                    method="POST"
                                    data-confirm-delete
                                    data-delete-label="catatan"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" aria-label="Hapus catatan {{ $note->title }}">⌫</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($notes->hasPages())
                <div class="pagination">
                    @if($notes->onFirstPage())
                        <span class="page-disabled">← Sebelumnya</span>
                    @else
                        <a href="{{ $notes->previousPageUrl() }}">← Sebelumnya</a>
                    @endif
                    <span>Halaman {{ $notes->currentPage() }} dari {{ $notes->lastPage() }}</span>
                    @if($notes->hasMorePages())
                        <a href="{{ $notes->nextPageUrl() }}">Selanjutnya →</a>
                    @else
                        <span class="page-disabled">Selanjutnya →</span>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">✎</div>
                <h3>{{ $search ? 'Catatan tidak ditemukan' : 'Belum ada catatan' }}</h3>
                <p>{{ $search ? 'Coba gunakan kata kunci yang berbeda.' : 'Mulai tulis cerita atau ide pertamamu sekarang.' }}</p>
                @if(!$search)
                    <a href="{{ route('notes.create') }}" class="button button-primary">＋ Buat Catatan Pertama</a>
                @endif
            </div>
        @endif
    </section>
</div>

@endsection

@push('scripts')
<script>
    const realtimeClock = document.querySelector('[data-realtime-clock]');

    if (realtimeClock) {
        const updateClock = () => {
            const now = new Date();
            realtimeClock.textContent = new Intl.DateTimeFormat(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(now);
            realtimeClock.dateTime = now.toISOString();
        };

        updateClock();
        window.setInterval(updateClock, 1000);
    }
</script>
@endpush