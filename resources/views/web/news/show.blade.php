@extends('web.layouts.app')

@section('title', ($post->seo_title ?: $post->title).' | '.config('app.name'))
@section('meta_description', $post->seo_description ?: $post->excerpt)
@section('meta_og_image', $post->coverMedia ? media_asset_url($post->coverMedia) : '')

@php
    $heroImage = $hero?->media ? media_asset_url($hero->media) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
@endphp

@section('content')
    <section class="page-hero news-page-hero news-detail-hero">
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
                    <a href="{{ route('web.quality') }}">Calidad</a>
                    <a href="{{ route('web.clients.index') }}">Clientes</a>
                    <a href="{{ route('web.partners.index') }}">Representadas</a>
                    <a href="{{ route('web.news.index') }}" class="is-active">Novedades</a>
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
                <a href="{{ route('web.quality') }}">Calidad</a>
                <a href="{{ route('web.clients.index') }}">Clientes</a>
                <a href="{{ route('web.partners.index') }}">Representadas</a>
                <a href="{{ route('web.news.index') }}" class="is-active">Novedades</a>
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
                <img src="{{ $heroImage }}" alt="{{ $hero?->title ?? 'Novedades' }}">
            </div>
        @endif

        <div class="page-hero__overlay"></div>

        <div class="container page-hero__content">
            <div class="page-hero__crumbs">
                <span>
                    <a href="{{ route('web.home') }}">Inicio</a> &gt;
                    <a href="{{ route('web.news.index') }}">Novedades</a> &gt;
                    <span class="is-current">{{ $post->title }}</span>
                </span>
            </div>
            <h1>{{ $hero?->title ?? 'NOVEDADES' }}</h1>
        </div>
    </section>

    <article class="news-detail">
        <div class="container news-detail__inner">
            <div class="news-detail__top">
                <div class="news-detail__meta">
                    <span class="news-detail__eyebrow">{{ strtoupper($post->category?->name ?? 'Novedad') }}</span>
                    @if($post->published_at)
                        <span class="news-detail__date">{{ $post->published_at->translatedFormat('d \\d\\e F \\d\\e Y') }}</span>
                    @endif
                    @if($post->author_name)
                        <span class="news-detail__author">{{ $post->author_name }}</span>
                    @endif
                </div>

                @if($post->excerpt)
                    <div class="news-detail__excerpt">{{ $post->excerpt }}</div>
                @endif
            </div>

            @if($post->coverMedia)
                <div class="news-detail__cover">
                    <img src="{{ media_asset_url($post->coverMedia) }}" alt="{{ $post->title }}">
                </div>
            @endif

            @if($post->content)
                <div class="news-detail__content">
                    {!! cms_rich_text($post->content) !!}
                </div>
            @endif

            @if($related->isNotEmpty())
                <section class="news-detail__related">
                    <h2>Otras novedades</h2>
                    <div class="news-page__grid news-page__grid--related">
                        @foreach($related as $item)
                            <article class="news-page__card">
                                <a href="{{ route('web.news.show', $item->slug) }}" class="news-page__card-link">
                                    <div class="news-page__card-media">
                                        @if($item->coverMedia)
                                            <img src="{{ media_asset_url($item->coverMedia) }}" alt="{{ $item->title }}">
                                        @endif
                                    </div>
                                    <div class="news-page__card-body">
                                        <div class="news-page__eyebrow">
                                            {{ strtoupper($item->category?->name ?? 'Novedad') }}
                                        </div>
                                        <h3>{{ $item->title }}</h3>
                                        <p>{{ $item->excerpt }}</p>
                                        <span class="news-page__card-more">Leer más</span>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </article>

    @include('web.about._footer')
@endsection
