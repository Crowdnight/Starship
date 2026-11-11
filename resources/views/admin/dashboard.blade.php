@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')
<div class="admin-page">
    <header class="admin-hero">
        <div>
            <span class="admin-eyebrow">STARSHIP · ADMIN PANEL</span>
            <h1>Halo, {{ auth()->user()->name }}!</h1>
            <p>Ringkasan komunitas Starship hari ini.</p>
        </div>
        <span class="admin-hero-mark" aria-hidden="true">✦</span>
    </header>

    <section class="admin-stat-grid" aria-label="Ringkasan Starship">
        <article class="stat-card">
            <div class="stat-icon purple" aria-hidden="true">♙</div>
            <div><span>Pengguna</span><h2>{{ number_format($totalUsers, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">●</div>
            <div><span>Akun Aktif</span><h2>{{ number_format($activeUsers, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon purple" aria-hidden="true">▤</div>
            <div><span>Catatan Publik</span><h2>{{ number_format($publicNoteCount, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">☷</div>
            <div><span>Komentar Publik</span><h2>{{ number_format($publicCommentCount, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon pink" aria-hidden="true">✉</div>
            <div><span>Pesan Belum Dibalas</span><h2>{{ number_format($pendingSupportCount, 0, ',', '.') }}</h2></div>
        </article>
    </section>

    <section class="note-form admin-panel" aria-labelledby="admin-shortcuts-title">
        <div class="admin-panel-heading">
            <div><h2 id="admin-shortcuts-title">Pintasan Admin</h2><p>Buka halaman pengelolaan yang sering digunakan.</p></div>
        </div>
        <div class="admin-shortcuts">
            <a class="admin-shortcut" href="{{ route('admin.statistics') }}"><span>Statistik komunitas</span><span aria-hidden="true">→</span></a>
            <a class="admin-shortcut" href="{{ route('admin.users.index') }}"><span>Kelola pengguna</span><span aria-hidden="true">→</span></a>
            <a class="admin-shortcut" href="{{ route('admin.content.index') }}"><span>Moderasi konten</span><span aria-hidden="true">→</span></a>
            <a class="admin-shortcut" href="{{ route('admin.support.index') }}"><span>Pesan masuk ({{ $pendingSupportCount }})</span><span aria-hidden="true">→</span></a>
            <a class="admin-shortcut" href="{{ route('admin.public-notes.index') }}"><span>Catatan publik</span><span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="note-form admin-panel" aria-labelledby="admin-recent-activity-title">
        <div class="admin-panel-heading">
            <div><h2 id="admin-recent-activity-title">Aktivitas terbaru</h2><p>Aktivitas akun dan konten publik saja.</p></div>
        </div>
        <div class="admin-feed">
            @forelse($latestActivities as $activity)
                <article class="admin-feed-item">
                    <div>
                        <a href="{{ $activity['url'] }}">{{ $activity['label'] }}</a>
                        <p>{{ $activity['detail'] }}</p>
                    </div>
                    <time datetime="{{ $activity['created_at']->toIso8601String() }}">
                        {{ $activity['created_at']->format('d M Y, H:i') }}
                    </time>
                </article>
            @empty
                <p class="admin-empty">Belum ada aktivitas komunitas.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
