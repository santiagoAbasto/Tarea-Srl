@extends('web.layouts.app')

@section('title', 'Productos | '.config('app.name'))

@php
    $heroImage = $hero?->media ? media_asset_url($hero->media) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : ($catalogHeaderLogoUrl ?? $headerLogoUrl ?? null);
@endphp

@section('content')
    <section class="products-catalog-page">
        <header class="site-header site-header--catalog" id="mainNav">
            <div class="container site-header__inner">
                <a href="{{ route('web.home') }}" class="brand">
                    @if($catalogHeaderLogo)
                        <img src="{{ $catalogHeaderLogoUrl }}" alt="{{ $catalogHeaderLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}" class="brand__logo">
                    @elseif($headerLogo)
                        <img src="{{ $headerLogoUrl }}" alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}" class="brand__logo">
                    @else
                        <span>{{ strtoupper($footer?->brand_name ?? 'Tarea') }}</span>
                    @endif
                </a>

                <nav class="nav">
                    <a href="{{ route('web.about') }}">Nosotros</a>
                    <a href="{{ route('web.products.index') }}" class="is-active">Productos</a>
                    <a href="{{ route('web.catalog.show') }}">Catalogo</a>
                    <a href="{{ route('web.quality') }}">Calidad</a>
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
                    @elseif($catalogHeaderLogo)
                        <img src="{{ $catalogHeaderLogoUrl }}" alt="{{ $catalogHeaderLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}" class="nav-mobile__logo">
                    @elseif($headerLogo)
                        <img src="{{ $headerLogoUrl }}" alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}" class="nav-mobile__logo">
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
                <a href="{{ route('web.products.index') }}" class="is-active">Productos</a>
                <a href="{{ route('web.catalog.show') }}">Catalogo</a>
                <a href="{{ route('web.quality') }}">Calidad</a>
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

        <div class="container products-catalog-page__crumbs">
            <div class="products-catalog-page__crumbs-inner">
                <span>
                    <a href="{{ route('web.home') }}">Inicio</a>
                    &gt;
                    <a href="{{ route('web.products.index') }}">Productos</a>
                    @if($mode === 'all')
                        &gt; <span class="is-current">Todos los productos</span>
                    @elseif($selectedFamily)
                        &gt; <span class="is-current">{{ $selectedFamily->name }}</span>
                    @endif
                </span>
            </div>
        </div>
    </section>

    <section class="products-catalog">
        <div class="container products-catalog__layout {{ $mode === 'all' ? 'products-catalog__layout--all' : '' }}">
            @if($mode !== 'all')
                <aside class="products-catalog__sidebar">
                    <a
                        href="{{ $selectedFamily ? route('web.products.family', $selectedFamily->slug) : route('web.products.all') }}"
                        class="products-catalog__sidebar-link {{ !$selectedSubfamily ? 'is-active' : '' }}"
                    >
                        <span class="products-catalog__sidebar-label">Todos los productos</span>
                    </a>

                    @foreach($sidebarItems as $item)
                        <a href="{{ route('web.products.family', $selectedFamily->slug) }}{{ $item->slug === ($selectedSubfamily->slug ?? null) ? '' : '?subfamily='.$item->slug }}" class="products-catalog__sidebar-link {{ $item->slug === ($selectedSubfamily->slug ?? null) ? 'is-active' : '' }}" style="--accent-color: {{ $item->accent_color ?: '#8BCFDE' }};">
                            <span class="products-catalog__sidebar-accent"></span>
                            <span class="products-catalog__sidebar-label">{{ $item->name }}</span>
                        </a>
                    @endforeach
                </aside>
            @endif

            <div class="products-catalog__content">
                @if($mode === 'all')
                    <form action="{{ route('web.products.all') }}" method="GET" class="products-catalog__search">
                        <label class="products-catalog__search-field" aria-label="Buscar productos">
                            <span class="products-catalog__search-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Buscar productos" autocomplete="off" data-products-search-input>
                        </label>
                    </form>
                @endif

                <div class="products-catalog__results" data-products-results>
                    @include('web.products._catalog-results', ['products' => $products, 'mode' => $mode])
                </div>
            </div>
        </div>
    </section>

    @include('web.about._footer')
@endsection

@if($mode === 'all')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('.products-catalog__search');
                const input = document.querySelector('[data-products-search-input]');
                const results = document.querySelector('[data-products-results]');

                if (!form || !input || !results) {
                    return;
                }

                let timer = null;
                let controller = null;
                const endpoint = @json(route('web.products.all'));

                const runSearch = function () {
                    const query = input.value.trim();

                    window.clearTimeout(timer);
                    timer = window.setTimeout(async function () {
                        if (controller) {
                            controller.abort();
                        }

                        controller = new AbortController();
                        const url = new URL(endpoint, window.location.origin);

                        if (query !== '') {
                            url.searchParams.set('q', query);
                        }

                        try {
                            const response = await fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html',
                                },
                                credentials: 'same-origin',
                                signal: controller.signal,
                            });

                            if (!response.ok) {
                                throw new Error('No se pudo buscar productos.');
                            }

                            results.innerHTML = await response.text();
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                console.error(error);
                            }
                        }
                    }, 220);
                };

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    runSearch();
                });

                ['input', 'keyup', 'search', 'change'].forEach(function (eventName) {
                    input.addEventListener(eventName, runSearch);
                });
            });
        </script>
    @endpush
@endif
