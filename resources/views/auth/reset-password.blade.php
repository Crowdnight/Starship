<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - {{ config('app.name') }}</title>
    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">
@include('shared.theme-toggle', ['floating' => true, 'showLabel' => true])

<div class="auth-wrapper">
    <div class="auth-decoration">
        <div class="auth-brand"><span>✦</span> {{ config('app.name') }}</div>
        <div class="auth-message">
            <span class="eyebrow">NEW PASSWORD</span>
            <h1>Pilih password baru untuk melanjutkan perjalananmu.</h1>
            <p>Gunakan password yang kuat dan belum pernah dipakai di akun ini.</p>
            <div class="auth-decoration-icon">✧</div>
        </div>
        <div class="auth-copyright">© {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    <div class="auth-panel">
        <div class="auth-form-wrapper">
            <div class="mobile-brand">✦ {{ config('app.name') }}</div>
            <span class="eyebrow">RESET PASSWORD</span>
            <h2>Buat password baru</h2>
            <p class="auth-subtitle">Tautan reset berlaku selama 60 menit.</p>

            @if($errors->any())
                <div class="alert-error" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password baru</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi password baru</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        required
                    >
                </div>

                <button type="submit" class="button button-primary auth-submit">
                    Simpan password baru →
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
