@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('page-heading', 'Detail Pengguna')

@section('content')
<div class="admin-page">
    <header class="admin-hero">
        <div>
            <span class="admin-eyebrow">STARSHIP · PROFIL PENGGUNA</span>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
        </div>
        <span class="admin-hero-mark" aria-hidden="true">♙</span>
    </header>

    <div class="admin-columns">
        <section class="note-form admin-panel">
            <div class="admin-panel-heading"><div><h2>Informasi akun</h2><p>Detail dan aktivitas profil pengguna.</p></div></div>
            <div class="admin-feed">
                <article class="admin-feed-item"><strong>Role</strong><span>{{ $user->isAdmin() ? 'Admin' : 'User' }}</span></article>
                <article class="admin-feed-item"><strong>Status</strong><span class="admin-status {{ $user->is_active ? '' : 'is-offline' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></article>
                <article class="admin-feed-item"><strong>Bergabung</strong><span>{{ $user->created_at->format('d M Y, H:i') }}</span></article>
                <article class="admin-feed-item"><strong>Terakhir online</strong><span>{{ $user->last_seen_at?->format('d M Y, H:i') ?? 'Belum tercatat' }}</span></article>
                <article class="admin-feed-item"><strong>Catatan publik</strong><span>{{ number_format($publicNoteCount, 0, ',', '.') }}</span></article>
                <article class="admin-feed-item"><strong>Komentar publik</strong><span>{{ number_format($publicCommentCount, 0, ',', '.') }}</span></article>
            </div>
        </section>

        <section class="note-form admin-panel">
            <div class="admin-panel-heading"><div><h2>Tindakan akun</h2><p>Kata sandi hanya dapat diubah pemilik melalui tautan reset.</p></div></div>
            @if(!$user->is(auth()->user()))
                <div class="admin-feed">
                    <form action="{{ route('admin.users.password-reset', $user) }}" method="POST" class="admin-feed-item">
                        @csrf
                        <div><strong>Kirim tautan reset</strong><p>Tautan dikirim ke alamat email akun ini.</p></div>
                        <button class="button button-light" type="submit">Kirim</button>
                    </form>

                    @if(!$user->isAdmin())
                        <form action="{{ route('admin.users.status', $user) }}" method="POST" class="admin-feed-item">
                            @csrf
                            @method('PATCH')
                            <div><strong>Status akun</strong><p>{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akses pengguna.</p></div>
                            <button class="button button-light" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="admin-feed-item" data-confirm-delete>
                            @csrf
                            @method('DELETE')
                            <div><strong>Hapus akun</strong><p>Akun pengguna akan dihapus.</p></div>
                            <button class="button button-danger" type="submit">Hapus</button>
                        </form>
                    @endif

                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="admin-feed-item">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="role">Role pengguna</label>
                            <select id="role" name="role">
                                <option value="user" @selected($user->role === 'user')>User</option>
                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                            </select>
                        </div>
                        <button class="button button-light" type="submit">Simpan Role</button>
                    </form>
                </div>
            @else
                <p class="admin-muted">Akun yang sedang digunakan tidak dapat diubah dari halaman ini.</p>
            @endif
            <a href="{{ route('admin.users.index') }}" class="button button-light" style="margin-top: 20px">← Kembali ke pengguna</a>
        </section>
    </div>
</div>
@endsection
