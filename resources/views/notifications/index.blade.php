@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-heading', 'Notifikasi')

@push('styles')
<style>
    .notifications-page {
        max-width: 900px;
        margin: 0 auto;
    }

    .notifications-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .notifications-heading h1 {
        margin: 6px 0;
        font-size: clamp(24px, 4vw, 32px);
        letter-spacing: -.8px;
    }

    .notifications-heading p {
        color: var(--muted);
        font-size: 13px;
    }

    .notification-list {
        display: grid;
        gap: 12px;
    }

    .notification-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--white);
        box-shadow: var(--shadow);
    }

    .notification-card.is-unread {
        border-color: color-mix(in srgb, var(--primary) 36%, var(--border));
        background: color-mix(in srgb, var(--primary-light) 35%, var(--white));
    }

    .notification-symbol {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 14px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 19px;
    }

    .notification-content {
        min-width: 0;
        flex: 1;
    }

    .notification-message {
        color: var(--text);
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .notification-note-title {
        display: block;
        margin-top: 5px;
        font-size: 13px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .notification-comment {
        margin-top: 7px;
        padding-left: 10px;
        border-left: 2px solid var(--border);
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .notification-time {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: 11px;
    }

    .notification-open-form {
        flex: 0 0 auto;
    }

    .notification-actions {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 8px;
    }

    .notification-dismiss-form {
        margin: 0;
    }

    .notification-dismiss-button {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--white);
        color: var(--muted);
        font-size: 19px;
        line-height: 1;
        cursor: pointer;
        transition: border-color .2s, color .2s, background .2s;
    }

    .notification-dismiss-button:hover,
    .notification-dismiss-button:focus-visible {
        border-color: var(--danger);
        background: color-mix(in srgb, var(--danger) 9%, var(--white));
        color: var(--danger);
        outline: none;
    }

    .notifications-empty {
        padding: 48px 24px;
        border: 1px dashed var(--border);
        border-radius: 18px;
        background: var(--white);
        text-align: center;
    }

    .notifications-empty-icon {
        display: grid;
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        place-items: center;
        border-radius: 18px;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 24px;
    }

    .notifications-empty h2 {
        margin-bottom: 7px;
        font-size: 18px;
    }

    .notifications-empty p {
        color: var(--muted);
        font-size: 13px;
    }

    @media (max-width: 600px) {
        .notifications-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .notification-card {
            flex-wrap: wrap;
            padding: 15px;
        }

        .notification-actions {
            width: 100%;
            margin-left: 56px;
        }

        .notification-open-form {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')
<main class="notifications-page">
    <header class="notifications-heading">
        <div>
            <span class="eyebrow">AKTIVITAS CATATANMU</span>
            <h1>Notifikasi</h1>
            <p>Kabar terbaru dari pengguna yang berinteraksi dengan catatanmu.</p>
        </div>
        @if(auth()->user()->unreadNotifications()->exists())
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="button button-light">Tandai semua dibaca</button>
            </form>
        @endif
    </header>

    @if($notifications->isEmpty())
        <section class="notifications-empty">
            <div class="notifications-empty-icon" aria-hidden="true">♢</div>
            <h2>Belum ada notifikasi</h2>
            <p>Like, dislike, dan komentar pada catatan publikmu akan muncul di sini.</p>
        </section>
    @else
        <div class="notification-list">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $activity = $data['activity'] ?? 'comment';
                    $symbol = match ($activity) {
                        'like' => '👍',
                        'dislike' => '👎',
                        default => '💬',
                    };
                    $message = match ($activity) {
                        'like' => 'menyukai catatanmu',
                        'dislike' => 'tidak menyukai catatanmu',
                        default => 'berkomentar pada catatanmu',
                    };
                @endphp
                <article class="notification-card {{ $notification->read_at ? '' : 'is-unread' }}">
                    <span class="notification-symbol" aria-hidden="true">{{ $symbol }}</span>
                    <div class="notification-content">
                        <p class="notification-message">
                            <strong>{{ $data['actor_name'] ?? 'Pengguna' }}</strong>
                            {{ $message }}
                        </p>
                        <span class="notification-note-title">{{ $data['note_title'] ?? 'Catatan' }}</span>
                        @if($activity === 'comment' && filled($data['comment_content'] ?? null))
                            <p class="notification-comment">{{ \Illuminate\Support\Str::limit($data['comment_content'], 240) }}</p>
                        @endif
                        <time class="notification-time" datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->diffForHumans() }}
                            @if(!$notification->read_at)
                                · Belum dibaca
                            @endif
                        </time>
                    </div>
                    <div class="notification-actions">
                        <form
                            action="{{ route('notifications.open', $notification->id) }}"
                            method="POST"
                            class="notification-open-form"
                        >
                            @csrf
                            <button type="submit" class="button button-light">Lihat catatan</button>
                        </form>
                        <form
                            action="{{ route('notifications.dismiss', $notification->id) }}"
                            method="POST"
                            class="notification-dismiss-form"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="notification-dismiss-button"
                                aria-label="Dismiss notifikasi dari {{ $data['actor_name'] ?? 'Pengguna' }}"
                                title="Dismiss notifikasi"
                            >×</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="public-pagination">{{ $notifications->links() }}</div>
        @endif
    @endif
</main>
@endsection
