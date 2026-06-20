@extends('layouts.site')

@php
    $isCategory = $kind === 'category';
    $title = ($isCategory ? 'Catégorie ' : 'Tag ').$taxonomy->name;
    $description = $taxonomy->description
        ?: ($isCategory
            ? "Tous les articles classés dans la catégorie « {$taxonomy->name} »."
            : "Tous les articles avec le tag « {$taxonomy->name} ».");
@endphp

@section('title', $title.' · Blog Vivre Pleinement')
@php
    $taxonomyUrl = $isCategory ? route('blog.category', $taxonomy->slug) : route('blog.tag', $taxonomy->slug);
@endphp
@section('canonical', $taxonomyUrl)
@section('description', $description)

@push('head')
    @if ($posts->currentPage() > 1)
        <meta name="robots" content="noindex, follow">
    @endif
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Blog', 'url' => route('blog.index')],
                ['label' => $taxonomy->name],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    {{ $isCategory ? 'Catégorie' : 'Tag' }}
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                    {{ $isCategory ? $taxonomy->name : '#'.$taxonomy->name }}
                </h1>
                @if ($taxonomy->description)
                    <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">{{ $taxonomy->description }}</p>
                @endif
                <p class="text-ink-muted mt-4 text-sm">
                    {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}
                </p>
            </div>
        </div>
    </header>

    <section class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @if ($posts->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" class="relative" />
                    @endforeach
                </div>

                <div class="mt-12">{{ $posts->links() }}</div>
            @else
                <div class="border-ink/15 rounded-3xl border border-dashed bg-white/60 p-12 text-center">
                    <p class="text-ink font-serif text-xl">Aucun article pour l'instant.</p>
                    <a href="{{ route('blog.index') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-teal-700 hover:text-teal-800">
                        Retour au blog
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            @endif
        </div>
    </section>
    </main>

    @include('home.sections.footer')
@endsection
