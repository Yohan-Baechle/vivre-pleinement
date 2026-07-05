@extends('layouts.site')

@section('robots', 'noindex, nofollow')

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <main id="main" class="bg-cream-50 min-h-screen pt-32 pb-16 sm:pt-36 sm:pb-20">
        @yield('student')
    </main>

    @include('home.sections.footer')
@endsection
