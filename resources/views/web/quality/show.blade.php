@extends('web.layouts.app')

@section('title', 'Calidad | '.config('app.name'))

@php
    $heroImage = $hero?->media ? media_asset_url($hero->media) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
    $policyFields = $policy?->fieldValues?->keyBy('field_key');
    $policyMediaType = (string) ($policyFields?->get('media_type')?->field_value ?? '');
    $policyImage = $policy?->media ? media_asset_url($policy->media) : null;
    $policyVideo = $policy?->secondaryMedia && str_starts_with((string) ($policy?->secondaryMedia?->mime_type ?? ''), 'video/')
        ? media_asset_url($policy->secondaryMedia)
        : null;
    $policyYoutubeUrl = trim((string) ($policyFields?->get('youtube_url')?->field_value ?? ''));
    $policyYoutubeEmbed = null;
    $policyYoutubeId = null;
    if ($policyYoutubeUrl !== '') {
        if (str_contains($policyYoutubeUrl, 'youtube.com/embed/') || str_contains($policyYoutubeUrl, 'youtube-nocookie.com/embed/')) {
            $policyYoutubeEmbed = $policyYoutubeUrl;
            if (preg_match('~/embed/([^?&/]+)~', $policyYoutubeUrl, $matches)) {
                $policyYoutubeId = $matches[1];
            }
        } elseif (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([^&?/]+)~', $policyYoutubeUrl, $matches)) {
            $policyYoutubeId = $matches[1];
            $policyYoutubeEmbed = 'https://www.youtube-nocookie.com/embed/'.$policyYoutubeId.'?rel=0&modestbranding=1&enablejsapi=1';
        }
    }
    if ($policyYoutubeEmbed) {
        $policyYoutubeEmbed .= (str_contains($policyYoutubeEmbed, '?') ? '&' : '?')
            .'autoplay=1&mute=1&playsinline=1&controls=1'
            .($policyYoutubeId ? '&loop=1&playlist='.$policyYoutubeId : '');
    }
    $policyHasVideo = $policyMediaType === 'youtube' ? filled($policyYoutubeEmbed) : filled($policyVideo);
    $logoItems = $logos?->items?->filter(fn ($item) => $item->media && $item->is_active) ?? collect();
    $isRichText = static fn ($value) => preg_match('/<[^>]+>/', (string) $value) === 1;
@endphp

@section('content')
    <section class="page-hero quality-page-hero">
        <header class="site-header" id="mainNav">
            <div class="container site-header__inner">
                <a href="{{ route('web.home') }}" class="brand">
                    @if($headerLogo)
                        <img
                            src="{{ $headerLogoUrl }}"
                            alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                            class="brand__logo"
                        >
                    @else
                        <span>{{ strtoupper($footer?->brand_name ?? 'Tarea') }}</span>
                    @endif
                </a>

                <nav class="nav">
                    <a href="{{ route('web.about') }}">Nosotros</a>
                    <a href="{{ route('web.products.index') }}">Productos</a>
                    <a href="{{ route('web.catalog.show') }}">Catalogo</a>
                    <a href="{{ route('web.quality') }}" class="is-active">Calidad</a>
                    <a href="{{ route('web.clients.index') }}">Clientes</a>
                    <a href="{{ route('web.partners.index') }}">Representadas</a>
                    <a href="{{ route('web.news.index') }}">Novedades</a>
                    <a href="{{ route('web.contact.show') }}">Contacto</a>
                    <span class="nav__search" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                            <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <a href="{{ route('web.quote.show') }}" class="nav__cta">Presupuesto</a>
                </nav>

                <button
                    id="hamburgerBtn"
                    type="button"
                    class="hamburger"
                    aria-expanded="false"
                    aria-controls="navLinks"
                    aria-label="Abrir menú de navegación"
                >
                    <span class="hamburger__line"></span>
                    <span class="hamburger__line"></span>
                    <span class="hamburger__line"></span>
                </button>
            </div>
        </header>

        <div id="navLinks" class="nav-mobile" role="dialog" aria-modal="true" aria-label="Menú principal">
            <div class="nav-mobile__header">
                <a href="{{ route('web.home') }}" class="brand nav-mobile__brand">
                    @if($darkLogoUrl)
                        <img
                            src="{{ $darkLogoUrl }}"
                            alt="{{ $darkLogo?->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                            class="nav-mobile__logo"
                        >
                    @elseif($headerLogo)
                        <img
                            src="{{ $headerLogoUrl }}"
                            alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                            class="nav-mobile__logo"
                        >
                    @else
                        <span>{{ strtoupper($footer?->brand_name ?? 'TAREA') }}</span>
                    @endif
                </a>
                <button id="navClose" type="button" class="nav-mobile__close" aria-label="Cerrar menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <nav class="nav-mobile__nav" aria-label="Navegación principal">
                <a href="{{ route('web.about') }}">Nosotros</a>
                <a href="{{ route('web.products.index') }}">Productos</a>
                <a href="{{ route('web.catalog.show') }}">Catalogo</a>
                <a href="{{ route('web.quality') }}" class="is-active">Calidad</a>
                <a href="{{ route('web.clients.index') }}">Clientes</a>
                <a href="{{ route('web.partners.index') }}">Representadas</a>
                <a href="{{ route('web.news.index') }}">Novedades</a>
                <a href="{{ route('web.contact.show') }}">Contacto</a>
                <a href="{{ route('web.search.index') }}" class="nav-mobile__search" aria-label="Ir a búsqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Buscar</span>
                </a>
                <a href="{{ route('web.quote.show') }}" class="nav-mobile__cta">Presupuesto</a>
            </nav>
        </div>
        <div id="navOverlay" class="nav-overlay" aria-hidden="true"></div>

        @if($heroImage)
            <div class="page-hero__media">
                <img src="{{ $heroImage }}" alt="{{ $hero?->title ?? 'Calidad' }}">
            </div>
        @endif

        <div class="page-hero__overlay"></div>

        <div class="container page-hero__content">
            <div class="page-hero__crumbs">
                <span><a href="{{ route('web.home') }}">Inicio</a> &gt; <span class="is-current">Calidad</span></span>
            </div>
            <h1>{{ $hero?->title ?? 'CALIDAD' }}</h1>
        </div>
    </section>

    <section class="quality-page">
        <div class="quality-page__content">
            <div class="quality-page__grid">
                <div class="quality-page__copy">
                    <h2 class="quality-page__title">{{ $policy?->title ?? 'Políticas de calidad' }}</h2>

                    <div class="quality-page__text">
                        @if($isRichText($policy?->description))
                            {!! cms_rich_text($policy?->description) !!}
                        @else
                            @foreach(explode("\n\n", trim((string) ($policy?->description ?? ''))) as $paragraph)
                                @if(trim($paragraph) !== '')
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="quality-page__logos-block">
                        <h2 class="quality-page__title">{{ $logos?->title ?? 'Logos de calidad' }}</h2>

                        @if($logoItems->isNotEmpty())
                            <div class="quality-page__logos">
                                @foreach($logoItems as $item)
                                    <article class="quality-page__logo-item">
                                        <img src="{{ media_asset_url($item->media) }}" alt="{{ $item->title ?? 'Logo de calidad' }}">
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if($policyImage || $policyVideo || $policyYoutubeEmbed)
                    <div class="quality-page__media {{ $policyHasVideo ? 'quality-page__media--interactive is-playing' : '' }}" data-quality-media>
                        @if($policyMediaType === 'youtube' && $policyYoutubeEmbed)
                            <iframe
                                src="{{ $policyYoutubeEmbed }}"
                                title="{{ $policy?->title ?? 'Políticas de calidad' }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                                data-quality-youtube
                            ></iframe>
                        @elseif($policyVideo)
                            <video controls autoplay muted loop playsinline data-quality-video>
                                <source src="{{ $policyVideo }}" type="{{ $policy?->secondaryMedia?->mime_type ?? 'video/mp4' }}">
                            </video>
                        @elseif($policyImage)
                            <img src="{{ $policyImage }}" alt="{{ $policy?->title ?? 'Políticas de calidad' }}">
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    @include('web.about._footer')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-quality-media]').forEach(function (media) {
                const video = media.querySelector('[data-quality-video]');
                const youtube = media.querySelector('[data-quality-youtube]');
                const toggle = media.querySelector('[data-quality-toggle]');

                if (!toggle) return;

                const setPaused = function (paused) {
                    media.classList.toggle('is-paused', paused);
                    media.classList.toggle('is-playing', !paused);
                };

                const toggleYoutube = function () {
                    if (!youtube || !youtube.contentWindow) return;

                    const playing = media.classList.contains('is-playing');
                    youtube.contentWindow.postMessage(JSON.stringify({
                        event: 'command',
                        func: playing ? 'pauseVideo' : 'playVideo',
                        args: []
                    }), '*');
                    setPaused(playing);
                };

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (video) {
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                        return;
                    }

                    toggleYoutube();
                });

                media.addEventListener('click', function (event) {
                    if (event.target === toggle || toggle.contains(event.target)) return;

                    if (video) {
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                        return;
                    }

                    if (youtube) {
                        toggleYoutube();
                    }
                });

                if (video) {
                    video.addEventListener('play', function () { setPaused(false); });
                    video.addEventListener('pause', function () { setPaused(true); });
                    video.addEventListener('ended', function () { setPaused(true); });
                }
            });
        });
    </script>
@endpush
