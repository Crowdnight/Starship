@extends('layouts.app')

@section('title', 'Riwayat Feedback')
@section('page-heading', 'Riwayat Feedback')

@section('content')
    <div class="form-heading">
        <h1>Riwayat Feedback</h1>
        <p>Sampaikan saran, laporan bug, atau pertanyaan. Balasan Admin dapat dibaca di percakapan ini.</p>
        <a href="{{ route('support.create') }}" class="button button-primary">+ Kirim Feedback</a>
    </div>

    @forelse($conversations as $conversation)
        @php($lastMessage = $conversation->messages->first())
        @php($awaitingAdmin = $lastMessage && (int) $lastMessage->sender_id === (int) $conversation->user_id)
        <article class="note-form support-history-card">
            <div class="support-history-content">
                <h2><a href="{{ route('support.show', $conversation) }}">{{ $conversation->subject }}</a></h2>
                <p>{{ $conversation->category_label }} · Dikirim {{ $conversation->created_at->format('d M Y, H:i') }}</p>
                <p>{{ \Illuminate\Support\Str::limit($lastMessage?->body ?? 'Belum ada pesan', 180) }}</p>
            </div>
            <span class="admin-status {{ $awaitingAdmin || ! $lastMessage ? 'is-offline' : '' }}">
                {{ $awaitingAdmin || ! $lastMessage ? 'Menunggu balasan' : 'Sudah dibalas' }}
            </span>
        </article>
    @empty
        <div class="note-form">
            <p>Belum ada feedback. Kirim feedback pertama Anda untuk memulai percakapan dengan Admin.</p>
        </div>
    @endforelse

    {{ $conversations->links() }}
@endsection
