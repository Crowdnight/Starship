@extends('layouts.app')

@section('title', 'Statistik')
@section('page-heading', 'Statistik')

@section('content')
<div class="admin-page">
    <header class="admin-hero">
        <div>
            <span class="admin-eyebrow">STARSHIP · ANALITIK</span>
            <h1>Statistik komunitas</h1>
            <p>Data pengguna, aktivitas publik, dan pesan yang tersimpan di Starship.</p>
        </div>
        <span class="admin-hero-mark" aria-hidden="true">◷</span>
    </header>

    <section class="admin-stat-grid" aria-label="Statistik Starship">
        <article class="stat-card">
            <div class="stat-icon purple" aria-hidden="true">♙</div>
            <div><span>Total Pengguna</span><h2>{{ number_format($totalUsers, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon blue" aria-hidden="true">●</div>
            <div><span>Akun Aktif</span><h2>{{ number_format($activeUsers, 0, ',', '.') }}</h2></div>
        </article>
        <article class="stat-card">
            <div class="stat-icon pink" aria-hidden="true">◉</div>
            <div><span>Online 5 Menit Terakhir</span><h2>{{ number_format($onlineUsers, 0, ',', '.') }}</h2></div>
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
            <div><span>Total Pesan Masuk</span><h2>{{ number_format($incomingMessageCount, 0, ',', '.') }}</h2></div>
        </article>
    </section>

    <section class="note-form admin-panel" aria-labelledby="admin-stats-filter-title">
        <div class="admin-panel-heading">
            <div>
                <h2 id="admin-stats-filter-title">Pertumbuhan dari waktu ke waktu</h2>
                <p>Jumlah data baru per hari dalam {{ $days }} hari terakhir.</p>
            </div>
            <form action="{{ route('admin.statistics') }}" method="GET" class="admin-filter">
                <label for="days">Rentang waktu
                    <select id="days" name="days">
                        <option value="7" @selected($days === 7)>7 hari</option>
                        <option value="14" @selected($days === 14)>14 hari</option>
                        <option value="30" @selected($days === 30)>30 hari</option>
                    </select>
                </label>
                <button class="button button-primary" type="submit">Terapkan</button>
            </form>
        </div>

        <div class="admin-chart-grid">
            <section class="note-form admin-panel admin-chart-panel" aria-labelledby="user-growth-title">
                <div class="admin-panel-heading">
                    <div><h2 id="user-growth-title">Pengguna baru</h2><p>Pendaftaran per hari</p></div>
                </div>
                <div class="admin-chart-scroll">
                    <div class="admin-chart" style="--chart-columns: {{ $chart->count() }}" role="img" aria-label="Grafik pengguna baru per hari">
                        @foreach($chart as $bucket)
                            <div class="admin-chart-column" title="{{ $bucket['label'] }}: {{ $bucket['users'] }} pengguna baru">
                                <div class="admin-chart-bar-area" aria-hidden="true">
                                    <span class="admin-chart-bar" style="height: {{ $bucket['users'] ? max(4, round($bucket['users'] / $userChartMax * 100)) : 0 }}%"></span>
                                </div>
                                <span class="admin-chart-label">{{ $bucket['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="note-form admin-panel admin-chart-panel" aria-labelledby="public-note-growth-title">
                <div class="admin-panel-heading">
                    <div><h2 id="public-note-growth-title">Catatan publik baru</h2><p>Catatan yang dibagikan per hari</p></div>
                </div>
                <div class="admin-chart-scroll">
                    <div class="admin-chart" style="--chart-columns: {{ $chart->count() }}" role="img" aria-label="Grafik catatan publik baru per hari">
                        @foreach($chart as $bucket)
                            <div class="admin-chart-column" title="{{ $bucket['label'] }}: {{ $bucket['notes'] }} catatan publik">
                                <div class="admin-chart-bar-area" aria-hidden="true">
                                    <span class="admin-chart-bar notes" style="height: {{ $bucket['notes'] ? max(4, round($bucket['notes'] / $noteChartMax * 100)) : 0 }}%"></span>
                                </div>
                                <span class="admin-chart-label">{{ $bucket['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </section>
</div>
@endsection
