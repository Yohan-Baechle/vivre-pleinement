@extends('youtube.oauth.layout')

@section('title', 'YouTube OAuth : Autorisation réussie')

@section('content')
    <h1 class="font-serif text-teal-700 text-3xl font-semibold">Autorisation réussie</h1>

    <p class="text-ink-soft mt-6">Copiez cette ligne dans votre fichier <strong class="text-ink">.env</strong> :</p>

    <code class="bg-cream-200 text-ink mt-3 block rounded-2xl px-4 py-3 font-mono text-sm break-all">
        YOUTUBE_OAUTH_REFRESH_TOKEN={{ $refreshToken }}
    </code>

    <p class="text-ink-soft mt-6">Puis lancez :</p>

    <code class="bg-cream-200 text-ink mt-3 block rounded-2xl px-4 py-3 font-mono text-sm break-all">
        vendor/bin/sail artisan youtube:fetch-transcripts --limit=3
    </code>

    <p class="text-ink-muted mt-8 text-sm">Vous pouvez fermer cet onglet.</p>
@endsection
