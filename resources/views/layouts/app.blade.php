
<!DOCTYPE html>
<html lang="id" @if(auth()->check() && auth()->user()->isAdmin() && request()->routeIs('admin.*')) data-admin-panel="true" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', config('app.name')) - {{ config('app.name') }}</title>

    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="{{ auth()->check() && auth()->user()->isAdmin() && request()->routeIs('admin.*') ? 'admin-panel-theme' : '' }}">

<div class="app-wrapper">

    <aside class="sidebar">

        <a href="{{ auth()->check() ? route('notes.index') : route('public-notes.index') }}" class="brand">
            <span class="brand-icon">✦</span>
            <span>{{ config('app.name') }}</span>
        </a>

        <div class="sidebar-label">MENU UTAMA</div>

        <nav class="sidebar-nav">

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span>▦</span> Dashboard
                    </a>
                @endif

                <a href="{{ route('notes.index') }}"
                   class="{{ request()->routeIs('notes.index') ? 'active' : '' }}">
                    <span>▦</span> Semua Catatan
                </a>

                <a href="{{ route('notes.create') }}"
                   class="{{ request()->routeIs('notes.create') ? 'active' : '' }}">
                    <span>＋</span> Buat Catatan
                </a>

                @if(auth()->user()->isAdmin())
                    <div class="sidebar-label">ADMIN PANEL</div>
                    <a href="{{ route('admin.statistics') }}"
                       class="{{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                        <span>◷</span> Statistik
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span>♙</span> Kelola Pengguna
                    </a>
                    <a href="{{ route('admin.content.index') }}"
                       class="{{ request()->routeIs('admin.content.*', 'admin.notes.*', 'admin.comments.*') ? 'active' : '' }}">
                        <span>▤</span> Moderasi Konten
                    </a>
                    <a href="{{ route('admin.support.index') }}"
                       class="{{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                        <span>✉</span> Pesan Masuk
                    </a>
                    <a href="{{ route('admin.public-notes.index') }}"
                       class="{{ request()->routeIs('admin.public-notes.*') ? 'active' : '' }}">
                        <span>🌍</span> Catatan Publik
                    </a>
                @endif
            @endauth

            @guest
                <a href="{{ route('public-notes.index') }}"
                   class="{{ request()->routeIs('public-notes.*') ? 'active' : '' }}">
                    <span>🌍</span> Catatan Publik
                </a>
            @endguest

            @auth
                @if(! auth()->user()->isAdmin())
                    <a href="{{ route('public-notes.index') }}"
                       class="{{ request()->routeIs('public-notes.*') ? 'active' : '' }}">
                        <span>🌍</span> Catatan Publik
                    </a>
                    <a href="{{ route('support.create') }}"
                       class="{{ request()->routeIs('support.*') ? 'active' : '' }}">
                        <span>✉</span> Kirim Feedback
                    </a>
                @endif
            @endauth

        </nav>

        <div class="sidebar-bottom">

            @auth
                <a href="{{ route('profile.edit') }}"
                   class="sidebar-profile sidebar-profile-link"
                   aria-label="Pengaturan profil {{ auth()->user()->name }}">
                    @include('users._avatar', [
                        'user' => auth()->user(),
                        'class' => 'avatar',
                    ])

                    <div class="profile-info">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->email }}</small>
                    </div>
                </a>
            @else
                <div class="sidebar-profile">
                    <div class="avatar">?</div>

                    <div class="profile-info">
                        <strong>Pengunjung</strong>
                        <small>Belum login</small>
                    </div>
                </div>
            @endauth

        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <div>
                <span class="topbar-label">PERSONAL WORKSPACE</span>
                <h2>@yield('page-heading', 'Dashboard')</h2>
            </div>

            <div class="topbar-actions">

                @include('shared.theme-toggle', ['showLabel' => true])

                @auth
                    @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
                    <a
                        href="{{ route('notifications.index') }}"
                        class="topbar-notification-button {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                        aria-label="Notifikasi{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' belum dibaca' : '' }}"
                        title="Notifikasi"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>
                        @if($unreadNotificationCount)
                            <span class="topbar-notification-count" aria-hidden="true">
                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('notes.export') }}"
                       class="button button-light">
                        ↓ Export CSV
                    </a>

                    <a href="{{ route('notes.create') }}"
                       class="button button-primary">
                        + Catatan Baru
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button type="submit" class="button button-danger">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="button button-light">
                        Login
                    </a>

                    <a href="{{ route('register') }}"
                       class="button button-primary">
                        Daftar Akun
                    </a>
                @endauth

            </div>

        </header>

        <section class="page-content">

            @if(session('success'))
                <div class="alert-success app-toast" role="status" aria-live="polite" data-toast>
                    <span class="app-toast-icon" aria-hidden="true">✓</span>
                    <span class="app-toast-message">{{ session('success') }}</span>
                    <button class="app-toast-close" type="button" aria-label="Tutup notifikasi">×</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <strong>Terjadi kesalahan:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </section>

        <footer class="footer">
            {{ config('app.name') }} © {{ date('Y') }}. Made with Laravel.
        </footer>

    </main>

</div>

@include('shared.delete-confirmation')

@stack('scripts')

<script>
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        let dismissTimer;
        const dismiss = () => {
            window.clearTimeout(dismissTimer);
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 220);
        };

        toast.querySelector('.app-toast-close').addEventListener('click', dismiss);
        dismissTimer = window.setTimeout(dismiss, 6000);
    });

    const deleteDialog = document.querySelector('#delete-confirm-dialog');
    const deleteTitle = deleteDialog?.querySelector('#delete-confirm-title');
    const deleteDescription = deleteDialog?.querySelector('#delete-confirm-description');
    const deleteSubmitButton = deleteDialog?.querySelector('[data-delete-submit]');
    const confirmedDeleteForms = new WeakSet();
    let pendingDeleteForm = null;
    let pendingDeleteSubmitter = null;
    let pendingDeleteItem = null;
    let deleteTrigger = null;

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm-delete], form[data-confirm-when-checked]');
        if (!form) return;

        if (confirmedDeleteForms.has(form)) {
            confirmedDeleteForms.delete(form);
            return;
        }

        const confirmWhenChecked = form.dataset.confirmWhenChecked;
        if (confirmWhenChecked) {
            const control = form.querySelector(`[name="${confirmWhenChecked}"]`);
            const replacesAvatar = form.querySelector('[name="avatar"]')?.files.length > 0;
            if (!control?.checked || replacesAvatar) return;
        }

        event.preventDefault();
        pendingDeleteForm = form;
        pendingDeleteSubmitter = event.submitter;
        pendingDeleteItem = form.closest('[data-delete-item]');
        deleteTrigger = document.activeElement;
        const label = form.dataset.deleteLabel || 'item';
        deleteTitle.textContent = `Hapus ${label}?`;
        deleteDescription.textContent = form.dataset.deleteDescription
            || `${label.charAt(0).toUpperCase()}${label.slice(1)} yang dihapus tidak dapat dikembalikan.`;

        form.closest('details')?.removeAttribute('open');
        deleteDialog.showModal();
        deleteDialog.querySelector('[data-delete-cancel]').focus();
    });

    deleteDialog?.querySelector('[data-delete-cancel]').addEventListener('click', () => {
        deleteDialog.close();
    });

    deleteDialog?.addEventListener('click', (event) => {
        if (event.target === deleteDialog) deleteDialog.close();
    });

    deleteDialog?.addEventListener('close', () => {
        if (pendingDeleteItem) pendingDeleteItem.classList.remove('is-removing');
        pendingDeleteForm = null;
        pendingDeleteSubmitter = null;
        pendingDeleteItem = null;
        deleteSubmitButton.disabled = false;
        deleteSubmitButton.textContent = 'Ya, hapus';
        if (deleteTrigger?.isConnected) deleteTrigger.focus();
        deleteTrigger = null;
    });

    deleteSubmitButton?.addEventListener('click', () => {
        if (!pendingDeleteForm) return;

        const form = pendingDeleteForm;
        const submitter = pendingDeleteSubmitter;
        const item = pendingDeleteItem;
        pendingDeleteForm = null;
        pendingDeleteSubmitter = null;
        pendingDeleteItem = null;

        deleteSubmitButton.disabled = true;
        deleteSubmitButton.textContent = 'Menghapus...';
        item?.classList.add('is-removing');
        deleteDialog.close();

        const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 220;
        window.setTimeout(() => {
            confirmedDeleteForms.add(form);
            if (submitter && submitter.form === form) form.requestSubmit(submitter);
            else form.requestSubmit();
        }, delay);
    });
</script>

</body>
</html>
