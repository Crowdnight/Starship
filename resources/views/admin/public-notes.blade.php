@extends('layouts.app')

@section('title', 'Catatan Publik')
@section('page-heading', 'Catatan Publik')

@section('content')
<div class="admin-page">
    <header class="admin-hero">
        <div>
            <span class="admin-eyebrow">STARSHIP · KOMUNITAS</span>
            <h1>Catatan Publik</h1>
            <p>Daftar catatan yang dibagikan secara publik oleh pengguna.</p>
        </div>
        <span class="admin-hero-mark" aria-hidden="true">✦</span>
    </header>

    <section class="note-form admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>{{ number_format($notes->total(), 0, ',', '.') }} catatan</h2>
                <p>Catatan pribadi tidak ditampilkan di halaman ini.</p>
            </div>
            <a href="{{ route('admin.content.index') }}" class="button button-light">Buka Moderasi</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Catatan</th>
                        <th>Kreator</th>
                        <th>Kategori</th>
                        <th>Dipublikasikan</th>
                        <th>Komentar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr>
                            <td>
                                <strong><a href="{{ route('public-notes.show', $note) }}">{{ $note->title }}</a></strong>
                                <small>{{ \Illuminate\Support\Str::limit($note->content, 100) }}</small>
                            </td>
                            <td>{{ $note->user?->name ?? 'Pengguna dihapus' }}</td>
                            <td>{{ $note->category }}</td>
                            <td>{{ $note->created_at->format('d M Y') }}</td>
                            <td>{{ number_format($note->comments_count, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('admin.notes.destroy', $note) }}" method="POST" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-muted">Belum ada catatan publik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $notes->links() }}
    </section>
</div>
@endsection
