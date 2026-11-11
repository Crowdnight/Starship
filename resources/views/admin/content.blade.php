@extends('layouts.app')

@section('title', 'Moderasi Konten')
@section('page-heading', 'Moderasi Konten')

@section('content')
    <div class="admin-page">
        <header class="admin-hero">
            <div>
                <span class="admin-eyebrow">STARSHIP · MODERASI</span>
                <h1>Moderasi Konten</h1>
                <p>Tinjau catatan dan komentar yang dibagikan secara publik.</p>
            </div>
            <span class="admin-hero-mark" aria-hidden="true">▤</span>
        </header>

        <nav class="admin-shortcuts" aria-label="Bagian moderasi" style="margin-bottom: 20px">
            <a class="admin-shortcut" href="#public-notes"><span>Catatan publik</span><span aria-hidden="true">↓</span></a>
            <a class="admin-shortcut" href="#public-comments"><span>Komentar publik</span><span aria-hidden="true">↓</span></a>
        </nav>

        <section id="public-notes" class="note-form admin-panel">
            <div class="admin-panel-heading">
                <div><h2>Catatan publik</h2><p>{{ number_format($notes->total(), 0, ',', '.') }} catatan untuk ditinjau.</p></div>
                <a href="{{ route('admin.public-notes.index') }}" class="button button-light">Lihat daftar lengkap</a>
            </div>
            @forelse($notes as $note)
                <article class="admin-feed-item">
                    <div>
                        <a href="{{ route('public-notes.show', $note) }}">{{ $note->title }}</a>
                        <p>Oleh {{ $note->user?->name ?? 'Pengguna dihapus' }} · {{ $note->comments_count }} komentar · {{ $note->created_at->format('d M Y, H:i') }}</p>
                        <p>{{ \Illuminate\Support\Str::limit($note->content, 240) }}</p>
                    </div>
                    <form action="{{ route('admin.notes.destroy', $note) }}" method="POST" data-confirm-delete>
                        @csrf
                        @method('DELETE')
                        <button class="button button-danger" type="submit">Hapus</button>
                    </form>
                </article>
            @empty
                <p class="admin-empty">Tidak ada catatan publik.</p>
            @endforelse
            {{ $notes->links() }}
        </section>

        <section id="public-comments" class="note-form admin-panel">
            <div class="admin-panel-heading">
                <div><h2>Komentar publik</h2><p>{{ number_format($comments->total(), 0, ',', '.') }} komentar untuk ditinjau.</p></div>
            </div>
            @forelse($comments as $comment)
                <article class="admin-feed-item">
                    <div>
                        <strong>{{ $comment->user?->name ?? 'Pengguna dihapus' }}</strong>
                        <p>Pada <a href="{{ route('public-notes.show', $comment->note) }}">{{ $comment->note->title }}</a> · {{ $comment->created_at->format('d M Y, H:i') }}</p>
                        <p>{{ $comment->content }}</p>
                    </div>
                    <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" data-confirm-delete>
                        @csrf
                        @method('DELETE')
                        <button class="button button-danger" type="submit">Hapus</button>
                    </form>
                </article>
            @empty
                <p class="admin-empty">Tidak ada komentar untuk dimoderasi.</p>
            @endforelse
            {{ $comments->links() }}
        </section>
    </div>
@endsection
