<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: ($seo?->title ?: config('app.name'));
        $pageDescription = trim($__env->yieldContent('meta_description')) ?: ($seo?->description ?: null);
        $pageKeywords = trim($__env->yieldContent('meta_keywords')) ?: ($seo?->keywords ?: null);
        $pageOgImage = trim($__env->yieldContent('meta_og_image')) ?: ($seo?->og_image_url ?: default_seo_image_url());
        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
        $robots = trim($__env->yieldContent('meta_robots')) ?: 'index,follow';
        $faviconVersion = @filemtime(public_path('favicon.ico')) ?: '1';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVersion }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}?v={{ $faviconVersion }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}?v={{ $faviconVersion }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}?v={{ $faviconVersion }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}?v={{ $faviconVersion }}">
    <meta name="theme-color" content="#ffffff">
    @if($pageDescription)
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    @if($pageKeywords)
        <meta name="keywords" content="{{ $pageKeywords }}">
    @endif
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    @if($pageDescription)
        <meta property="og:description" content="{{ $pageDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($pageOgImage)
        <meta property="og:image" content="{{ $pageOgImage }}">
        <meta property="og:image:secure_url" content="{{ $pageOgImage }}">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $pageOgImage }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if($pageDescription)
        <meta name="twitter:description" content="{{ $pageDescription }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@400&display=swap" rel="stylesheet">
    @vite(['resources/css/web/app.css', 'resources/js/web.js'])
    @stack('head')
</head>
<body>
    <div class="page-shell">
        @yield('content')
    </div>
    @stack('scripts')
    <script>
        (function () {
            const triggers = document.querySelectorAll('.nav__search');
            const searchUrl = @json(route('web.search.index'));

            triggers.forEach(function (trigger) {
                trigger.setAttribute('role', 'button');
                trigger.setAttribute('tabindex', '0');
                trigger.setAttribute('aria-label', 'Ir a búsqueda');

                trigger.addEventListener('click', function () {
                    window.location.href = searchUrl;
                });
                trigger.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        window.location.href = searchUrl;
                    }
                });
            });
        })();
    </script>
</body>
</html>
