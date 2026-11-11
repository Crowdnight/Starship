<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - {{ config('app.name') }}</title>

    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">

@include('shared.theme-toggle', ['floating' => true, 'showLabel' => true])

<div class="auth-wrapper">

    <div class="auth-decoration">
        <span class="floating-shape shape-one"></span>
        <span class="floating-shape shape-two"></span>
        <span class="floating-shape shape-three"></span>

        <div class="auth-brand">
            <span>✦</span> {{ config('app.name') }}
        </div>

        <div class="auth-message">
            <span class="eyebrow">YOUR PERSONAL JOURNAL</span>

            <h1>Every day has a story worth remembering.</h1>

            <p>
                Simpan pemikiran, ide, dan perjalanan hidupmu
                dalam satu ruang pribadi.
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

            <span class="eyebrow">WELCOME BACK</span>

            <h2>Selamat datang kembali!</h2>

            <p class="auth-subtitle">
                Silakan masuk ke akunmu untuk melanjutkan.
            </p>

            @if(session('status'))
                <div class="alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">

                @csrf

                <div class="form-group">
                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <p class="auth-switch auth-forgot-password">
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                </p>

                <label class="remember-label">
                    <input type="checkbox" name="remember" value="1">
                    Ingat saya
                </label>

                <button type="submit" class="button button-primary auth-submit">
                    Masuk ke Akun →
                </button>

            </form>

            <a href="{{ route('public-notes.index') }}" class="button button-light auth-submit">
                Masuk sebagai tamu →
            </a>

            <p class="auth-switch">
                Belum memiliki akun?
                <a href="{{ route('register') }}">Daftar sekarang</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>