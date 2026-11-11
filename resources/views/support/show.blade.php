@extends('layouts.app')

@section('title', $conversation->subject)
@section('page-heading', $conversation->subject)

@section('content')
    @php($isAdmin = auth()->user()->isAdmin() && request()->routeIs('admin.*'))
    <div class="form-heading">
        <h1>{{ $conversation->subject }}</h1>
        @if($isAdmin)
            <p>{{ $conversation->category_label }} · Percakapan dengan {{ $conversation->user->name }} ({{ $conversation->user->email }})</p>
            <a href="{{ route('admin.support.index') }}" class="button button-light">← Daftar Pesan</a>
        @else
            <p>{{ $conversation->category_label }} · Percakapan dengan Admin</p>
            <a href="{{ route('support.index') }}" class="button button-light">← Pesan Saya</a>
        @endif
    </div>

    <div class="support-conversation">
        @foreach($conversation->messages as $message)
            <article class="note-form support-message {{ $message->sender->isAdmin() ? 'from-admin' : 'from-user' }}">
                <header>
                    <strong>{{ $message->sender->isAdmin() ? 'Admin' : $message->sender->name }}</strong>
                    <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('d M Y, H:i') }}</time>
                </header>
                <p>{{ $message->body }}</p>
            </article>
        @endforeach
    </div>

    <form
        action="{{ $isAdmin ? route('admin.support.reply', $conversation) : route('support.reply', $conversation) }}"
        method="POST"
        class="note-form"
    >
        @csrf
        <div class="form-group">
            <label for="body">{{ $isAdmin ? 'Balas Feedback' : 'Balas Percakapan' }}</label>
            <textarea id="body" name="body" rows="5" maxlength="5000" required>{{ old('body') }}</textarea>
        </div>
        <button type="submit" class="button button-primary">{{ $isAdmin ? 'Kirim Balasan' : 'Kirim Pesan' }}</button>
    </form>
@endsection
