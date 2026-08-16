@extends('youtube.oauth.layout')

@section('title', 'YouTube OAuth — Échec')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-red-700">Autorisation impossible</h1>

    <p class="text-ink-soft mt-6">{{ $message }}</p>
@endsection
