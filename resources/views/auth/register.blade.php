<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - {{ config('app.name') }}</title>

    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">

@include('shared.theme-toggle', ['floating' => true, 'showLabel' => true])

<div class="auth-wrapper">

    <div class="auth-decoration">

        <div class="auth-brand">
            <span>✦</span> {{ config('app.name') }}
        </div>

        <div class="auth-message">
            <span class="eyebrow">START YOUR JOURNEY</span>

            <h1>Your thoughts deserve their own space.</h1>

            <p>
                Buat akun dan mulai simpan setiap cerita
                serta ide yang berarti bagimu.
            </p>

            <div class="auth-decoration-icon">✧</div>
        </div>

        <div class="auth-copyright">
            © {{ date('Y') }} {{ config('app.name') }}
        </div>

    </div>

    <div class="auth-panel">

        <div class="auth-form-wrapper">

            <div class="mobile-brand">✦ {{ config('app.name') }}</div>

            <span class="eyebrow">CREATE YOUR ACCOUNT</span>

            <h2>Buat akun baru ✨</h2>

            <p class="auth-subtitle">
                Mulai perjalanan menulismu hari ini.
            </p>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">

                @csrf

                <div class="form-group">
                    <label>Nama Lengkap</label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Masukkan nama"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>

                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Ulangi password"
                        required
                    >
                </div>

                <button type="submit" class="button button-primary auth-submit">
                    Buat Akun →
                </button>

            </form>

            <p class="auth-switch">
                Sudah memiliki akun?
                <a href="{{ route('login') }}">Login di sini</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>