<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Se libérer des troubles anxieux | Laura Baechlé')</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @else
        <meta name="description" content="Anxiété généralisée, phobies, TOC, burnout ? Laura Baechlé vous accompagne en thérapie ACT, à distance, avec des outils concrets pour retrouver une vie sereine.">
    @endif
    <meta name="keywords" content="@yield('keywords', 'troubles anxieux, anxiété généralisée, TAG, phobies, TOC, accompagnement anxiété, se libérer de l\'anxiété, Laura Baechlé')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'Se libérer des troubles anxieux · Laura Baechlé')">
    <meta property="og:description" content="@yield('og_description', 'Tous les outils, pas à pas, pour les personnes souffrant d\'anxiété généralisée, de phobies ou de TOC. Accompagnement par Laura Baechlé.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:image" content="@yield('og_image', asset('images/laura-portrait-1200.webp'))">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-cream-50 text-ink antialiased">
    @yield('body')
    <x-cookie-banner />
    @stack('scripts')
</body>
</html>
