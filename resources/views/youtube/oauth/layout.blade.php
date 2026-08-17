<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-cream-50 text-ink antialiased">
    <main class="mx-auto max-w-2xl px-4 py-16 sm:py-24">
        @yield('content')
    </main>
</body>
</html>
