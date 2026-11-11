@extends('layouts.app')

@section('title', 'Kirim Feedback')
@section('page-heading', 'Kirim Feedback')

@section('content')
    <div class="form-heading">
        <h1>Kirim Feedback</h1>
        <p>Bagikan saran, laporan bug, atau pertanyaan agar kami dapat meningkatkan Daily Notes.</p>
        <a href="{{ route('support.index') }}" class="button button-light">Riwayat Feedback</a>
    </div>

    <form action="{{ route('support.store') }}" method="POST" class="note-form">
        @csrf
        <div class="form-group">
            <label for="category">Kategori</label>
            <select id="category" name="category" required>
                @foreach(\App\Models\SupportConversation::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', 'suggestion') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="subject">Subjek feedback</label>
            <input id="subject" name="subject" value="{{ old('subject') }}" maxlength="150" required>
        </div>
        <div class="form-group">
            <label for="body">Isi feedback</label>
            <textarea id="body" name="body" rows="7" maxlength="5000" required>{{ old('body') }}</textarea>
        </div>
        <div class="form-actions">
            <a href="{{ route('support.index') }}" class="button button-light">Batal</a>
            <button type="submit" class="button button-primary">Kirim Feedback</button>
        </div>
    </form>
@endsection
