@extends('layouts.app')

@section('title', 'Pesan Masuk')
@section('page-heading', 'Pesan Masuk')

@section('content')
    <div class="admin-page">
        <header class="admin-hero">
            <div>
                <span class="admin-eyebrow">STARSHIP · DUKUNGAN</span>
                <h1>Pesan Masuk</h1>
                <p>Baca percakapan, lihat pesan terakhir, dan balas langsung ke pengguna.</p>
            </div>
            <span class="admin-hero-mark" aria-hidden="true">✉</span>
        </header>

        <section class="note-form admin-panel">
            <div class="admin-panel-heading">
                <div><h2>{{ number_format($conversations->total(), 0, ',', '.') }} percakapan</h2><p>Status menunjukkan siapa yang mengirim pesan terakhir.</p></div>
            </div>
            <div class="admin-feed">
                @forelse($conversations as $conversation)
                    @php($lastMessage = $conversation->messages->first())
                    @php($awaitingAdmin = $lastMessage && (int) $lastMessage->sender_id === (int) $conversation->user_id)
                    <article class="admin-feed-item">
                        <div>
                            <a href="{{ route('admin.support.show', $conversation) }}">{{ $conversation->subject }}</a>
                            <p>{{ $conversation->category_label }} · Dari {{ $conversation->user->name }} ({{ $conversation->user->email }})</p>
                            <p>{{ \Illuminate\Support\Str::limit($lastMessage?->body ?? 'Belum ada pesan', 220) }}</p>
                            <time datetime="{{ ($lastMessage?->created_at ?? $conversation->updated_at)->toIso8601String() }}">
                                Pesan terakhir · {{ ($lastMessage?->created_at ?? $conversation->updated_at)->format('d M Y, H:i') }}
                            </time>
                        </div>
                        <span class="admin-status {{ $awaitingAdmin ? 'is-offline' : '' }}">
                            {{ $awaitingAdmin ? 'Menunggu balasan' : 'Sudah dibalas' }}
                        </span>
                    </article>
                @empty
                    <p class="admin-empty">Belum ada pesan dari pengguna.</p>
                @endforelse
            </div>
            {{ $conversations->links() }}
        </section>
    </div>
@endsection
