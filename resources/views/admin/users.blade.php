@extends('layouts.app')

@section('title', 'Kelola Pengguna')
@section('page-heading', 'Kelola Pengguna')

@section('content')
<div class="admin-page">
    <header class="admin-hero">
        <div>
            <span class="admin-eyebrow">STARSHIP · ADMIN PANEL</span>
            <h1>Kelola Pengguna</h1>
            <p>Cari akun, lihat aktivitas terakhir, dan kelola status atau role dengan aman.</p>
        </div>
        <span class="admin-hero-mark" aria-hidden="true">♙</span>
    </header>

    <section class="note-form admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>{{ number_format($users->total(), 0, ',', '.') }} pengguna</h2>
                <p>{{ $adminCount }} akun admin · Penghapusan akun admin dan pengubahan akun sendiri dilindungi.</p>
            </div>
        </div>

        <form action="{{ route('admin.users.index') }}" method="GET" class="admin-search">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email..." aria-label="Cari nama atau email pengguna">
            <button type="submit" class="button button-primary">Cari</button>
            @if($search)
                <a href="{{ route('admin.users.index') }}" class="button button-light">Reset</a>
            @endif
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Terakhir online</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }}</small>
                            </td>
                            <td>{{ $user->isAdmin() ? 'Admin' : 'User' }}</td>
                            <td><span class="admin-status {{ $user->is_active ? '' : 'is-offline' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>{{ $user->last_seen_at?->format('d M Y, H:i') ?? 'Belum tercatat' }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a class="button button-light" href="{{ route('admin.users.show', $user) }}">Detail</a>
                                    @if(!$user->is(auth()->user()))
                                        <form action="{{ route('admin.users.password-reset', $user) }}" method="POST">
                                            @csrf
                                            <button class="button button-light" type="submit">Reset Password</button>
                                        </form>
                                        @if(!$user->isAdmin())
                                            <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button class="button button-light" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" data-confirm-delete>
                                                @csrf
                                                @method('DELETE')
                                                <button class="button button-danger" type="submit">Hapus</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-muted">Tidak ada pengguna yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </section>
</div>
@endsection
