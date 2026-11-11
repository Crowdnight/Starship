@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page-heading', 'Pengaturan Profil')

@section('content')
<div class="profile-page">
    <header class="profile-page-heading">
        <div>
            <span class="eyebrow">AKUN SAYA</span>
            <h1>Pengaturan Profil</h1>
            <p>Atur identitas dan tampilan yang mewakili dirimu.</p>
        </div>
        <span class="profile-heading-mark" aria-hidden="true">✦</span>
    </header>

    <form
        action="{{ route('profile.update') }}"
        method="POST"
        class="note-form profile-form"
        enctype="multipart/form-data"
        data-confirm-when-checked="remove_avatar"
        data-delete-label="foto profil"
        data-delete-description="Foto profil akan dihapus dan avatar kembali menggunakan warna pilihanmu."
    >
        @csrf
        @method('PUT')

        <dialog class="avatar-crop-dialog" data-avatar-crop-dialog aria-labelledby="avatar-crop-title">
            <div class="avatar-crop-panel">
                <div class="avatar-crop-heading">
                    <div>
                        <span class="eyebrow">ATUR FOTO PROFIL</span>
                        <h2 id="avatar-crop-title">Sesuaikan foto</h2>
                        <p>Geser foto ke arah mana saja, lalu atur zoom sampai pas.</p>
                    </div>
                    <button type="button" class="avatar-crop-close" data-crop-cancel aria-label="Tutup pemotong foto">×</button>
                </div>

                <div class="avatar-crop-stage" data-crop-stage tabindex="0" aria-label="Area crop foto. Geser gambar dengan pointer atau gunakan tombol panah pada keyboard.">
                    <img data-crop-image alt="Pratinjau crop foto profil">
                </div>

                <label class="avatar-crop-zoom">
                    <span>Zoom</span>
                    <input type="range" min="1" max="3" step="0.01" value="1" data-crop-zoom>
                </label>
                <p class="avatar-crop-error" data-crop-error role="alert" hidden></p>

                <div class="avatar-crop-actions">
                    <button type="button" class="button button-light" data-crop-cancel>Batal</button>
                    <button type="button" class="button button-primary" data-crop-apply>Terapkan crop</button>
                </div>
            </div>
        </dialog>

        <section class="profile-hero" aria-label="Pratinjau profil">
            <span class="profile-hero-sparkle" aria-hidden="true">✦</span>
            <div class="profile-avatar-preview">
                <div class="profile-avatar-control">
                    @include('users._avatar', [
                        'user' => $user,
                        'class' => 'profile-avatar-large',
                    ])
                    <button
                        type="button"
                        class="profile-avatar-upload"
                        data-avatar-upload-trigger
                        aria-label="Pilih foto profil"
                        title="Pilih foto profil"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h3l1.5-2h7L17 7h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/>
                            <circle cx="12" cy="13" r="3.5"/>
                        </svg>
                        <span>Ganti foto</span>
                    </button>
                    <input
                        class="profile-avatar-file"
                        id="avatar"
                        type="file"
                        name="avatar"
                        accept="image/jpeg,image/png,image/webp"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                </div>
                <div>
                    <span class="profile-hero-caption">PROFIL {{ strtoupper(config('app.name')) }}</span>
                    <strong data-profile-name>{{ $user->name }}</strong>
                    <span class="profile-hero-email">{{ $user->email }}</span>
                    <small class="profile-avatar-hint">Arahkan ke foto untuk mengganti</small>
                </div>
            </div>
            @error('avatar') <small class="profile-error">{{ $message }}</small> @enderror
            <span class="profile-active-pill"><span></span>Akun aktif</span>
        </section>

        <section class="profile-section profile-identity" aria-labelledby="profile-identity-title">
            <div class="profile-section-heading">
                <span class="profile-section-icon" aria-hidden="true">01</span>
                <div>
                    <h2 id="profile-identity-title">Informasi akun</h2>
                    <p>Nama dapat dilihat oleh orang lain saat kamu berbagi.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Nama</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                @error('name') <small class="profile-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group profile-email-group">
                <label for="profile-email">Email</label>
                <div class="profile-email-value" id="profile-email" aria-readonly="true">{{ $user->email }}</div>
                <small class="profile-help">Email akun disimpan sebagai informasi dan tidak dapat diubah di sini.</small>
            </div>
        </section>

        <section class="profile-section" aria-labelledby="profile-appearance-title">
            <div class="profile-section-heading">
                <span class="profile-section-icon" aria-hidden="true">02</span>
                <div>
                    <h2 id="profile-appearance-title">Tampilan profil</h2>
                    <p>Atur warna avatar dan foto yang digunakan di {{ config('app.name') }}.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="avatar_color">Warna Avatar</label>
                <div class="profile-color-picker">
                    <input
                        id="avatar_color"
                        type="color"
                        name="avatar_color"
                        value="{{ old('avatar_color', $user->avatar_color ?? '#4775BF') }}"
                        required
                    >
                    <div class="profile-color-options">
                        @foreach([
                            ['#4775BF', 'Biru'],
                            ['#7655D9', 'Ungu'],
                            ['#E65D75', 'Merah muda'],
                            ['#E88A3A', 'Oranye'],
                            ['#258D68', 'Hijau'],
                            ['#242B3D', 'Navy'],
                        ] as [$color, $name])
                            <button
                                type="button"
                                class="profile-color-swatch {{ strtoupper($user->avatar_color ?? '#4775BF') === $color ? 'is-selected' : '' }}"
                                style="background-color: {{ $color }}"
                                data-avatar-color="{{ $color }}"
                                aria-label="{{ $name }}"
                                title="{{ $name }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
                <small class="profile-help">Warna avatar juga menjadi garis tepi foto profilmu.</small>
                @error('avatar_color') <small class="profile-error">{{ $message }}</small> @enderror
            </div>

            @if($user->avatar_path)
                <label class="profile-remove-photo">
                    <input type="checkbox" name="remove_avatar" value="1" {{ old('remove_avatar') ? 'checked' : '' }}>
                    <span>
                        <strong>Hapus foto profil</strong>
                        <small>Gunakan warna avatar sebagai gantinya.</small>
                    </span>
                </label>
            @endif
        </section>

        <div class="profile-form-actions">
            <a href="{{ route('notes.index') }}" class="button button-light">Batal</a>
            <button type="submit" class="button button-primary">Simpan Profil</button>
        </div>
    </form>

    <section class="profile-danger-zone" data-delete-item aria-labelledby="delete-account-title">
        <div>
            <span class="profile-danger-eyebrow">ZONA BERBAHAYA</span>
            <h2 id="delete-account-title">Hapus akun</h2>
            <p>Akun, semua catatan, komentar, dan foto profilmu akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <form
            action="{{ route('profile.destroy') }}"
            method="POST"
            data-confirm-delete
            data-delete-label="akun"
            data-delete-description="Akun, semua catatan, komentar, dan foto profilmu akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="button button-danger">Hapus Akun</button>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
    .avatar-crop-dialog {
        width: min(94vw, 460px);
        max-width: none;
        max-height: min(92vh, 760px);
        padding: 0;
        overflow: auto;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--white);
        color: var(--text);
        box-shadow: 0 24px 80px rgba(0, 0, 0, .28);
    }

    .avatar-crop-dialog::backdrop {
        background: rgba(15, 17, 15, .72);
        backdrop-filter: blur(5px);
    }

    .avatar-crop-panel {
        display: grid;
        gap: 18px;
        padding: 24px;
    }

    .avatar-crop-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .avatar-crop-heading h2 {
        margin: 4px 0 5px;
        font-size: 21px;
    }

    .avatar-crop-heading p {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .avatar-crop-close {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--input-surface);
        color: var(--text);
        font-size: 22px;
        cursor: pointer;
    }

    .avatar-crop-stage {
        position: relative;
        width: min(100%, 340px);
        aspect-ratio: 1;
        justify-self: center;
        overflow: hidden;
        border: 3px solid var(--primary);
        border-radius: 50%;
        background-color: #242424;
        background-image: linear-gradient(45deg, #303030 25%, transparent 25%), linear-gradient(-45deg, #303030 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #303030 75%), linear-gradient(-45deg, transparent 75%, #303030 75%);
        background-position: 0 0, 0 8px, 8px -8px, -8px 0;
        background-size: 16px 16px;
        cursor: grab;
        touch-action: none;
    }

    .avatar-crop-stage.is-dragging {
        cursor: grabbing;
    }

    .avatar-crop-stage img {
        position: absolute;
        max-width: none;
        max-height: none;
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    .avatar-crop-zoom {
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: center;
        gap: 14px;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
    }

    .avatar-crop-zoom input {
        width: 100%;
        accent-color: var(--primary);
    }

    .avatar-crop-error {
        color: var(--danger);
        font-size: 12px;
    }

    .avatar-crop-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @media (max-width: 480px) {
        .avatar-crop-panel {
            padding: 18px;
        }

        .avatar-crop-actions {
            flex-direction: column-reverse;
        }

        .avatar-crop-actions .button {
            width: 100%;
        }
    }

    .profile-page {
        max-width: 860px;
        margin: 0 auto;
        animation: profilePageEnter .45s ease both;
    }

    .profile-page-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .profile-page-heading h1 {
        margin-top: 7px;
        font-size: clamp(24px, 4vw, 32px);
        letter-spacing: -.8px;
    }

    .profile-page-heading p,
    .profile-section-heading p {
        margin-top: 6px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .profile-heading-mark {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid color-mix(in srgb, var(--primary) 22%, var(--border));
        border-radius: 16px;
        background: linear-gradient(145deg, var(--primary-light), var(--white));
        color: var(--primary-dark);
        font-size: 24px;
        box-shadow: var(--shadow);
    }

    .profile-form {
        padding: clamp(20px, 4vw, 34px);
        overflow: hidden;
        animation: profileCardEnter .5s .04s ease both;
    }

    .profile-hero {
        position: relative;
        display: flex;
        min-height: 150px;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: calc(clamp(20px, 4vw, 34px) * -1);
        margin-bottom: 28px;
        padding: 27px clamp(20px, 4vw, 34px);
        overflow: hidden;
        border-bottom: 1px solid var(--border);
        background:
            radial-gradient(ellipse at 82% 15%, color-mix(in srgb, var(--primary) 23%, transparent), transparent 35%),
            linear-gradient(115deg, var(--welcome-start), var(--welcome-end));
    }

    .profile-hero::after {
        position: absolute;
        right: 12%;
        bottom: -72px;
        width: 180px;
        height: 180px;
        border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);
        border-radius: 50%;
        content: "";
        pointer-events: none;
    }

    .profile-hero-sparkle {
        position: absolute;
        top: 17px;
        right: 28%;
        color: var(--primary);
        font-size: 19px;
        opacity: .8;
        animation: profileSparkle 3s ease-in-out infinite;
    }

    .profile-hero .profile-avatar-preview {
        position: relative;
        z-index: 1;
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 18px;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .profile-avatar-large {
        width: 82px;
        height: 82px;
        flex: 0 0 auto;
        border: 3px solid var(--white);
        border-radius: 50%;
        box-shadow: 0 0 0 3px var(--avatar-color, var(--primary)), 0 8px 20px rgba(0, 0, 0, .14);
        font-size: 25px;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .profile-avatar-control {
        position: relative;
        width: 82px;
        height: 82px;
        flex: 0 0 auto;
        border-radius: 50%;
    }

    .profile-avatar-control .profile-avatar-large {
        width: 100%;
        height: 100%;
    }

    .profile-avatar-upload {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border: 0;
        border-radius: 50%;
        background: rgba(25, 27, 24, .72);
        color: #fff;
        font: inherit;
        font-size: 10px;
        font-weight: 800;
        opacity: 0;
        cursor: pointer;
        transition: opacity .2s ease, transform .2s ease;
    }

    .profile-avatar-upload svg {
        width: 21px;
        height: 21px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.8;
    }

    .profile-avatar-control:hover .profile-avatar-upload,
    .profile-avatar-control:focus-within .profile-avatar-upload {
        opacity: 1;
    }

    .profile-avatar-control:hover .profile-avatar-large {
        transform: translateY(-2px) scale(1.03);
    }

    .profile-avatar-upload:focus-visible {
        opacity: 1;
        outline: 3px solid var(--primary);
        outline-offset: 4px;
    }

    .profile-avatar-file {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
        white-space: nowrap;
    }

    .profile-hero .profile-avatar-preview strong,
    .profile-hero .profile-avatar-preview span {
        display: block;
        overflow-wrap: anywhere;
    }

    .profile-hero .profile-avatar-preview strong {
        margin-top: 3px;
        color: var(--text);
        font-size: 20px;
        letter-spacing: -.5px;
    }

    .profile-hero-caption {
        color: var(--primary-dark) !important;
        font-size: 9px !important;
        font-weight: 800;
        letter-spacing: 1.15px;
    }

    .profile-hero-email {
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px;
    }

    .profile-avatar-hint {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 10px;
    }

    .profile-active-pill {
        position: relative;
        z-index: 1;
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 7px;
        padding: 8px 11px;
        border: 1px solid color-mix(in srgb, var(--success) 22%, transparent);
        border-radius: 999px;
        background: color-mix(in srgb, var(--white) 74%, transparent);
        color: var(--text);
        font-size: 10px;
        font-weight: 700;
    }

    .profile-active-pill span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--success) 16%, transparent);
    }

    .profile-section + .profile-section {
        margin-top: 28px;
        padding-top: 27px;
        border-top: 1px solid var(--border);
    }

    .profile-section-heading {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 22px;
    }

    .profile-section-icon {
        display: grid;
        width: 32px;
        height: 32px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 10px;
        font-weight: 800;
    }

    .profile-section-heading h2 {
        font-size: 16px;
        letter-spacing: -.2px;
    }

    .profile-avatar-preview {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 15px;
        background: var(--input-surface);
    }

    .profile-email-value {
        display: flex;
        min-height: 48px;
        align-items: center;
        padding: 12px 15px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: var(--input-surface);
        color: var(--muted);
        font-size: 13px;
        overflow-wrap: anywhere;
        user-select: text;
    }

    .profile-color-picker,
    .profile-color-options {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .profile-color-picker input[type="color"] {
        width: 50px;
        height: 44px;
        padding: 4px;
        cursor: pointer;
    }

    .profile-color-swatch {
        width: 29px;
        height: 29px;
        border: 2px solid var(--white);
        border-radius: 50%;
        box-shadow: 0 0 0 1px var(--border);
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .profile-color-swatch.is-selected {
        box-shadow: 0 0 0 2px var(--white), 0 0 0 4px var(--primary);
    }

    .profile-color-swatch:hover,
    .profile-color-swatch:focus-visible {
        transform: scale(1.12);
        box-shadow: 0 0 0 2px var(--primary);
        outline: none;
    }

    .profile-color-swatch.is-selected:hover,
    .profile-color-swatch.is-selected:focus-visible {
        box-shadow: 0 0 0 2px var(--white), 0 0 0 4px var(--primary);
    }

    .profile-help {
        display: block;
        margin-top: 8px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.6;
    }

    .profile-error {
        display: block;
        margin-top: 7px;
        color: var(--danger);
    }

    .profile-form .profile-remove-photo {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin: 0;
        padding: 14px;
        border: 1px solid color-mix(in srgb, var(--danger) 23%, var(--border));
        border-radius: 12px;
        background: color-mix(in srgb, var(--danger) 5%, var(--white));
        cursor: pointer;
    }

    .profile-remove-photo input[type="checkbox"] {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
        margin-top: 2px;
        accent-color: var(--danger);
    }

    .profile-remove-photo strong,
    .profile-remove-photo small {
        display: block;
    }

    .profile-remove-photo strong {
        font-size: 12px;
    }

    .profile-remove-photo small {
        margin-top: 4px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .profile-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .profile-danger-zone {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        margin-top: 22px;
        padding: 22px;
        border: 1px solid color-mix(in srgb, var(--danger) 32%, var(--border));
        border-radius: 16px;
        background: color-mix(in srgb, var(--danger) 5%, var(--white));
        transition: border-color .2s ease;
    }

    .profile-danger-zone:hover {
        border-color: color-mix(in srgb, var(--danger) 55%, var(--border));
    }

    .profile-danger-eyebrow {
        color: var(--danger);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1.2px;
    }

    .profile-danger-zone h2 {
        margin-top: 5px;
        font-size: 16px;
    }

    .profile-danger-zone p {
        max-width: 470px;
        margin-top: 6px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.6;
    }

    .profile-danger-zone .button {
        flex: 0 0 auto;
    }

    @keyframes profilePageEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes profileCardEnter {
        from { opacity: 0; transform: translateY(14px) scale(.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes profileSparkle {
        0%, 100% { opacity: .45; transform: scale(.85) rotate(-12deg); }
        50% { opacity: 1; transform: scale(1.12) rotate(12deg); }
    }

    @media (max-width: 600px) {
        .profile-page-heading {
            align-items: flex-start;
        }

        .profile-heading-mark {
            width: 40px;
            height: 40px;
            border-radius: 13px;
            font-size: 20px;
        }

        .profile-hero {
            min-height: 130px;
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }

        .profile-hero .profile-avatar-preview {
            gap: 14px;
        }

        .profile-avatar-control {
            width: 68px;
            height: 68px;
        }

        .profile-avatar-large {
            width: 68px;
            height: 68px;
            font-size: 21px;
        }

        .profile-hero .profile-avatar-preview strong {
            font-size: 17px;
        }

        .profile-avatar-upload {
            font-size: 9px;
        }

        .profile-avatar-upload svg {
            width: 18px;
            height: 18px;
        }

        .profile-active-pill {
            margin-top: -6px;
            margin-left: 82px;
        }

        .profile-form-actions {
            flex-direction: column-reverse;
        }

        .profile-form-actions .button {
            width: 100%;
        }

        .profile-danger-zone {
            align-items: stretch;
            flex-direction: column;
        }

        .profile-danger-zone .button {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .profile-page,
        .profile-form,
        .profile-hero-sparkle {
            animation: none;
        }

        .profile-avatar-large,
        .profile-avatar-upload,
        .profile-color-swatch,
        .profile-danger-zone {
            transition: none;
        }
    }

    @media (hover: none) {
        .profile-avatar-upload {
            opacity: 1;
            background: rgba(25, 27, 24, .55);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const avatarColorInput = document.querySelector('#avatar_color');
    const profileAvatar = document.querySelector('.profile-avatar-preview .user-avatar');
    const avatarInput = document.querySelector('#avatar');
    const cropDialog = document.querySelector('[data-avatar-crop-dialog]');
    const cropStage = document.querySelector('[data-crop-stage]');
    const cropImage = document.querySelector('[data-crop-image]');
    const cropZoom = document.querySelector('[data-crop-zoom]');
    const cropError = document.querySelector('[data-crop-error]');
    const cropApplyButton = document.querySelector('[data-crop-apply]');
    const cropCancelButtons = document.querySelectorAll('[data-crop-cancel]');
    let cropState = null;
    let previousPreviewUrl = null;
    let lastCroppedFile = null;

    const updateSelectedSwatch = (color) => {
        document.querySelectorAll('[data-avatar-color]').forEach((swatch) => {
            const selected = swatch.dataset.avatarColor.toUpperCase() === color.toUpperCase();
            swatch.classList.toggle('is-selected', selected);
            swatch.setAttribute('aria-pressed', String(selected));
        });
    };

    document.querySelectorAll('[data-avatar-color]').forEach((swatch) => {
        swatch.setAttribute('aria-pressed', String(swatch.classList.contains('is-selected')));
        swatch.addEventListener('click', () => {
            avatarColorInput.value = swatch.dataset.avatarColor;
            profileAvatar.style.setProperty('--avatar-color', swatch.dataset.avatarColor);
            updateSelectedSwatch(swatch.dataset.avatarColor);
        });
    });

    avatarColorInput.addEventListener('input', () => {
        profileAvatar.style.setProperty('--avatar-color', avatarColorInput.value);
        updateSelectedSwatch(avatarColorInput.value);
    });

    document.querySelector('#name').addEventListener('input', (event) => {
        document.querySelector('[data-profile-name]').textContent = event.target.value || 'Nama kamu';
    });

    document.querySelector('[data-avatar-upload-trigger]').addEventListener('click', () => {
        avatarInput.click();
    });

    const clampCropPosition = () => {
        if (!cropState) return;
        const bounds = cropStage.getBoundingClientRect();
        cropState.x = Math.min(0, Math.max(bounds.width - cropState.width, cropState.x));
        cropState.y = Math.min(0, Math.max(bounds.height - cropState.height, cropState.y));
        cropImage.style.width = `${cropState.width}px`;
        cropImage.style.height = `${cropState.height}px`;
        cropImage.style.left = `${cropState.x}px`;
        cropImage.style.top = `${cropState.y}px`;
    };

    const setCropZoom = (zoom) => {
        if (!cropState) return;
        const bounds = cropStage.getBoundingClientRect();
        const centerX = bounds.width / 2;
        const centerY = bounds.height / 2;
        const imageX = (centerX - cropState.x) / cropState.width;
        const imageY = (centerY - cropState.y) / cropState.height;
        const scale = Math.max(bounds.width / cropState.naturalWidth, bounds.height / cropState.naturalHeight) * zoom;
        cropState.width = cropState.naturalWidth * scale;
        cropState.height = cropState.naturalHeight * scale;
        cropState.x = centerX - imageX * cropState.width;
        cropState.y = centerY - imageY * cropState.height;
        cropState.zoom = zoom;
        clampCropPosition();
    };

    const openCropDialog = (file) => {
        const previewUrl = URL.createObjectURL(file);
        const image = new Image();
        image.onload = () => {
            cropDialog.showModal();
            window.requestAnimationFrame(() => {
                const bounds = cropStage.getBoundingClientRect();
                const scale = Math.max(bounds.width / image.naturalWidth, bounds.height / image.naturalHeight);
                cropState = {
                    naturalWidth: image.naturalWidth,
                    naturalHeight: image.naturalHeight,
                    width: image.naturalWidth * scale,
                    height: image.naturalHeight * scale,
                    x: 0,
                    y: 0,
                    zoom: 1,
                    previewUrl,
                };
                cropImage.src = previewUrl;
                cropZoom.value = '1';
                cropError.hidden = true;
                cropState.x = (bounds.width - cropState.width) / 2;
                cropState.y = (bounds.height - cropState.height) / 2;
                clampCropPosition();
            });
        };
        image.onerror = () => {
            URL.revokeObjectURL(previewUrl);
            const transfer = new DataTransfer();
            if (lastCroppedFile) transfer.items.add(lastCroppedFile);
            avatarInput.files = transfer.files;
            cropError.textContent = 'Foto tidak dapat dibuka. Silakan pilih gambar lain.';
            cropError.hidden = false;
        };
        image.src = previewUrl;
    };

    avatarInput.addEventListener('change', (event) => {
        const [file] = event.target.files;
        if (file) openCropDialog(file);
    });

    cropZoom.addEventListener('input', () => {
        setCropZoom(Number(cropZoom.value));
    });

    let pointerPosition = null;
    cropStage.addEventListener('pointerdown', (event) => {
        if (!cropState) return;
        pointerPosition = { x: event.clientX, y: event.clientY };
        cropStage.setPointerCapture(event.pointerId);
        cropStage.classList.add('is-dragging');
    });

    cropStage.addEventListener('pointermove', (event) => {
        if (!pointerPosition || !cropState) return;
        cropState.x += event.clientX - pointerPosition.x;
        cropState.y += event.clientY - pointerPosition.y;
        pointerPosition = { x: event.clientX, y: event.clientY };
        clampCropPosition();
    });

    const endCropDrag = () => {
        pointerPosition = null;
        cropStage.classList.remove('is-dragging');
    };
    cropStage.addEventListener('pointerup', endCropDrag);
    cropStage.addEventListener('pointercancel', endCropDrag);
    cropStage.addEventListener('lostpointercapture', endCropDrag);
    cropStage.addEventListener('keydown', (event) => {
        const offsets = {
            ArrowUp: [0, 10],
            ArrowDown: [0, -10],
            ArrowLeft: [10, 0],
            ArrowRight: [-10, 0],
        };
        const offset = offsets[event.key];
        if (!offset || !cropState) return;
        event.preventDefault();
        cropState.x += offset[0];
        cropState.y += offset[1];
        clampCropPosition();
    });

    cropCancelButtons.forEach((button) => {
        button.addEventListener('click', () => {
            cropDialog.close();
        });
    });

    cropDialog.addEventListener('close', () => {
        const previewUrl = cropState?.previewUrl;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        if (!cropState?.applied) {
            const transfer = new DataTransfer();
            if (lastCroppedFile) transfer.items.add(lastCroppedFile);
            avatarInput.files = transfer.files;
        }
        cropState = null;
    });

    cropApplyButton.addEventListener('click', () => {
        if (!cropState) return;

        const bounds = cropStage.getBoundingClientRect();
        const sourceScale = cropState.width / cropState.naturalWidth;
        const sourceX = Math.max(0, -cropState.x / sourceScale);
        const sourceY = Math.max(0, -cropState.y / sourceScale);
        const sourceSize = bounds.width / sourceScale;
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const context = canvas.getContext('2d');

        if (!context) {
            cropError.textContent = 'Browser tidak dapat memproses foto ini. Coba browser lain.';
            cropError.hidden = false;
            return;
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(
            cropImage,
            sourceX,
            sourceY,
            sourceSize,
            sourceSize,
            0,
            0,
            canvas.width,
            canvas.height
        );

        canvas.toBlob((blob) => {
            if (!blob) {
                cropError.textContent = 'Foto gagal diproses. Silakan atur crop dan coba lagi.';
                cropError.hidden = false;
                return;
            }

            const croppedFile = new File([blob], 'profile-crop.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            avatarInput.files = transfer.files;
            cropState.applied = true;
            lastCroppedFile = croppedFile;

            if (previousPreviewUrl) URL.revokeObjectURL(previousPreviewUrl);
            previousPreviewUrl = URL.createObjectURL(blob);
            profileAvatar.dataset.previewUrl = previousPreviewUrl;
            profileAvatar.replaceChildren(Object.assign(document.createElement('img'), {
                src: previousPreviewUrl,
                alt: 'Pratinjau foto profil yang sudah dipotong',
            }));
            profileAvatar.classList.add('user-avatar-with-photo');
            const removeAvatarCheckbox = document.querySelector('[name="remove_avatar"]');
            if (removeAvatarCheckbox) removeAvatarCheckbox.checked = false;
            document.querySelector('.profile-avatar-hint').textContent = 'Foto sudah dipotong dan siap disimpan';
            cropDialog.close();
        }, 'image/jpeg', 0.92);
    });
</script>
@endpush
