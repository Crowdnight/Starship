<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - {{ config('app.name') }}</title>
    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">
@include('shared.theme-toggle', ['floating' => true, 'showLabel' => true])

<div class="auth-wrapper">
    <div class="auth-decoration">
        <div class="auth-brand"><span>✦</span> {{ config('app.name') }}</div>
        <div class="auth-message">
            <span class="eyebrow">ACCOUNT RECOVERY</span>
            <h1>Ruang untuk ceritamu selalu bisa ditemukan kembali.</h1>
            <p>Kami akan mengirim tautan aman untuk mengatur ulang password akunmu.</p>
            <div class="auth-decoration-icon">✧</div>
        </div>
        <div class="auth-copyright">© {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    <div class="auth-panel">
        <div class="auth-form-wrapper">
            <div class="mobile-brand">✦ {{ config('app.name') }}</div>
            <span class="eyebrow">RESET PASSWORD</span>
            <h2>Lupa password?</h2>
            <p class="auth-subtitle">Masukkan email akunmu. Kami akan mengirim tautan reset password.</p>

            @if(session('status'))
                <div class="alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
                <button type="submit" class="button button-primary auth-submit">
                    Kirim tautan reset →
                </button>
            </form>

            <p class="auth-switch">
                Ingat password?
                <a href="{{ route('login') }}">Kembali ke login</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
