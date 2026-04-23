@extends('web.layouts.app')

@section('title', ($q ? 'Buscar: '.$q.' | ' : 'Buscar | ').config('app.name'))

@php
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
@endphp

@section('content')
    <section class="search-page">
        <header class="site-header site-header--catalog" id="mainNav">
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
                    <a href="{{ route('web.news.index') }}">Novedades</a>
                    <a href="{{ route('web.contact.show') }}">Contacto</a>
                    <span class="nav__search is-active" aria-hidden="true">
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
                <a href="{{ route('web.news.index') }}">Novedades</a>
                <a href="{{ route('web.contact.show') }}">Contacto</a>
                <a href="{{ route('web.search.index') }}" class="nav-mobile__search is-active" aria-label="Ir a búsqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Buscar</span>
                </a>
                <a href="{{ route('web.quote.show') }}" class="nav-mobile__cta">Presupuesto</a>
            </nav>
        </div>
        <div id="navOverlay" class="nav-overlay" aria-hidden="true"></div>

        <div class="container search-page__crumbs">
            <div class="search-page__crumbs-inner">
                <span>
                    <a href="{{ route('web.home') }}">Inicio</a>
                    &gt;
                    <span class="is-current">Buscar</span>
                </span>
            </div>
        </div>

        <section class="search-page__content">
            <div class="container">
                <div class="search-page__head">
                    <h1 class="search-page__title">Buscar</h1>
                    <form action="{{ route('web.search.index') }}" method="GET" class="search-page__form" role="search">
                        <label class="search-page__field" aria-label="Buscar en todo el sitio">
                            <span class="search-page__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                                    <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar en toda la web" autocomplete="off" data-global-search-input>
                        </label>
                    </form>
                </div>

                <div class="search-page__results" data-global-search-results>
                    @if($q === '')
                        <div class="search-page__empty">
                            Empezá a escribir y te mostramos coincidencias de toda la web, divididas por secciones.
                        </div>
                    @elseif($groups->isEmpty())
                        <div class="search-page__empty">
                            No encontramos coincidencias para “{{ $q }}”.
                        </div>
                    @else
                        @foreach($groups as $group)
                            <section class="search-page__group">
                                <div class="search-page__group-title">{{ $group['title'] }}</div>
                                <div class="search-page__group-list">
                                    @foreach($group['items'] as $item)
                                        <a class="search-page__item" href="{{ $item['url'] }}">
                                            <span class="search-page__item-title">{{ $item['title'] }}</span>
                                            @if(!empty($item['meta']))
                                                <span class="search-page__item-meta">{{ $item['meta'] }}</span>
                                            @endif
                                            @if(!empty($item['context']))
                                                <span class="search-page__item-slug">{{ $item['context'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>
    </section>

    @include('web.about._footer')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('[data-global-search-input]');
            const results = document.querySelector('[data-global-search-results]');

            if (!input || !results) {
                return;
            }

            let timer = null;
            let controller = null;
            const endpoint = @json(route('web.search.suggest'));

            const escapeHtml = function (value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            const renderEmpty = function (message) {
                results.innerHTML = '<div class="search-page__empty">' + escapeHtml(message) + '</div>';
            };

            const renderGroups = function (groups) {
                if (!groups.length) {
                    renderEmpty('No encontramos coincidencias. Probá con otra palabra clave.');
                    return;
                }

                results.innerHTML = groups.map(function (group) {
                    const items = group.items.map(function (item) {
                        return '' +
                            '<a class="search-page__item" href="' + escapeHtml(item.url) + '">' +
                                '<span class="search-page__item-title">' + escapeHtml(item.title) + '</span>' +
                                (item.meta ? '<span class="search-page__item-meta">' + escapeHtml(item.meta) + '</span>' : '') +
                                (item.context ? '<span class="search-page__item-slug">' + escapeHtml(item.context) + '</span>' : '') +
                            '</a>';
                    }).join('');

                    return '' +
                        '<section class="search-page__group">' +
                            '<div class="search-page__group-title">' + escapeHtml(group.title) + '</div>' +
                            '<div class="search-page__group-list">' + items + '</div>' +
                        '</section>';
                }).join('');
            };

            const runSearch = function () {
                const query = input.value.trim();
                const url = new URL(window.location.href);

                if (controller) {
                    controller.abort();
                }

                if (query === '') {
                    url.searchParams.delete('q');
                    window.history.replaceState({}, '', url);
                    renderEmpty('Empezá a escribir y te mostramos coincidencias de toda la web, divididas por secciones.');
                    return;
                }

                url.searchParams.set('q', query);
                window.history.replaceState({}, '', url);

                controller = new AbortController();

                fetch(endpoint + '?q=' + encodeURIComponent(query), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: controller.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('No se pudo buscar.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        renderGroups(Array.isArray(data.groups) ? data.groups : []);
                    })
                    .catch(function (error) {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        renderEmpty('No se pudo cargar la búsqueda en este momento.');
                    });
            };

            input.addEventListener('input', function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(runSearch, 180);
            });
        });
    </script>
@endpush
