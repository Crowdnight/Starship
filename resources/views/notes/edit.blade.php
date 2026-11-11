@extends('layouts.app')

@section('title', 'Edit Catatan')
@section('page-heading', 'Edit Catatan')

@section('content')
    @include('notes._form', [
        'editing' => true,
        'note' => $note,
    ])
@endsection
