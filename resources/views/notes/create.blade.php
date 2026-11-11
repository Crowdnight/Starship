@extends('layouts.app')

@section('title', 'Tambah Catatan')
@section('page-heading', 'Buat Catatan')

@section('content')
    @include('notes._form', [
        'editing' => false,
        'note' => null,
    ])
@endsection
