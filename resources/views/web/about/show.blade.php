@extends('web.layouts.app')

@section('title', 'Nosotros | '.config('app.name'))

@php
    $heroImage = $hero?->media ? media_asset_url($hero->media) : null;
    $introImage = $intro?->media ? media_asset_url($intro->media) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
    $missionItem = $missionVision?->items?->firstWhere('item_key', 'mission');
    $visionItem = $missionVision?->items?->firstWhere('item_key', 'vision');
    $missionItem = $missionItem?->is_active ? $missionItem : null;
    $visionItem = $visionItem?->is_active ? $visionItem : null;
    $valueItems = $values?->items?->filter(fn ($item) => $item->is_active) ?? collect();
    $membershipItems = $memberships?->items?->filter(fn ($item) => $item->media && $item->is_active) ?? collect();
    $isRichText = static fn ($value) => preg_match('/<[^>]+>/', (string) $value) === 1;
@endphp

@section('content')
    <section class="page-hero about-page-hero">
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
                    <a href="{{ route('web.about') }}" class="is-active">Nosotros</a>
                    <a href="{{ route('web.products.index') }}">Productos</a>
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
                <a href="{{ route('web.about') }}" class="is-active">Nosotros</a>
                <a href="{{ route('web.products.index') }}">Productos</a>
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

        @if($heroImage)
            <div class="page-hero__media">
                <img src="{{ $heroImage }}" alt="{{ $hero?->title ?? 'Nosotros' }}">
            </div>
        @endif

        <div class="page-hero__overlay"></div>

        <div class="container page-hero__content">
            <div class="page-hero__crumbs">
                <span><a href="{{ route('web.home') }}">Inicio</a> &gt; <span class="is-current">Nosotros</span></span>
            </div>
            <h1>{{ $hero?->title ?? 'NOSOTROS' }}</h1>
        </div>
    </section>

    @if($intro)
        <section class="about-page__intro">
            <div class="container about-page__intro-grid">
                <div class="about-page__intro-copy">
                    <h2>{{ $intro->title ?: 'Quienes somos' }}</h2>
                    <div class="about-page__intro-text">
                        @if($isRichText($intro->description))
                            {!! cms_rich_text($intro->description) !!}
                        @else
                            @foreach(explode("\n\n", trim((string) ($intro->description ?? ''))) as $paragraph)
                                @if(trim($paragraph) !== '')
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                @if($introImage)
                    <div class="about-page__intro-media">
                        <img src="{{ $introImage }}" alt="{{ $intro->title ?: 'Quienes somos' }}">
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($missionVision && ($missionItem || $visionItem))
        <section class="about-page__mission-vision">
            <div class="container about-page__mission-grid">
                @if($missionItem)
                    <article class="about-card about-card--mission">
                        <div class="about-card__heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                <path d="M30 55C43.8071 55 55 43.8071 55 30C55 16.1929 43.8071 5 30 5C16.1929 5 5 16.1929 5 30C5 43.8071 16.1929 55 30 55Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M30 45C38.2843 45 45 38.2843 45 30C45 21.7157 38.2843 15 30 15C21.7157 15 15 21.7157 15 30C15 38.2843 21.7157 45 30 45Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M30 35C32.7614 35 35 32.7614 35 30C35 27.2386 32.7614 25 30 25C27.2386 25 25 27.2386 25 30C25 32.7614 27.2386 35 30 35Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h2>{{ $missionItem->title ?? 'Misión' }}</h2>
                        </div>
                        <div class="about-card__text">
                            @if($isRichText($missionItem->description))
                                {!! cms_rich_text($missionItem->description) !!}
                            @else
                                @foreach(explode("\n\n", trim((string) ($missionItem->description ?? ''))) as $paragraph)
                                    @if(trim($paragraph) !== '')
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </article>
                @endif

                @if($visionItem)
                    <article class="about-card about-card--vision">
                        <div class="about-card__heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                <path d="M5 30C5 30 12.5 12.5 30 12.5C47.5 12.5 55 30 55 30C55 30 47.5 47.5 30 47.5C12.5 47.5 5 30 5 30Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M30 37.5C34.1421 37.5 37.5 34.1421 37.5 30C37.5 25.8579 34.1421 22.5 30 22.5C25.8579 22.5 22.5 25.8579 22.5 30C22.5 34.1421 25.8579 37.5 30 37.5Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h2>{{ $visionItem->title ?? 'Visión' }}</h2>
                        </div>
                        <div class="about-card__text">
                            @if($isRichText($visionItem->description))
                                {!! cms_rich_text($visionItem->description) !!}
                            @else
                                @foreach(explode("\n\n", trim((string) ($visionItem->description ?? ''))) as $paragraph)
                                    @if(trim($paragraph) !== '')
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </article>
                @endif
            </div>
        </section>
    @endif

    @if($values && $valueItems->isNotEmpty())
        <section class="about-page__values">
            <div class="container">
                <h2 class="about-page__section-title">{{ $values->title ?: 'Nuestros valores' }}</h2>
                <div class="about-values__grid">
                    @foreach($valueItems as $value)
                        <article class="about-value">
                            <div class="about-value__icon" aria-hidden="true">
                                @if($value->item_key === 'entrepreneurial-spirit')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <path d="M37.5 35C38 32.5 39.25 30.75 41.25 28.75C43.75 26.5 45 23.25 45 20C45 16.0218 43.4196 12.2064 40.6066 9.3934C37.7936 6.58035 33.9782 5 30 5C26.0218 5 22.2064 6.58035 19.3934 9.3934C16.5804 12.2064 15 16.0218 15 20C15 22.5 15.5 25.5 18.75 28.75C20.5 30.5 22 32.5 22.5 35M22.5 45H37.5M25 55H35" stroke="#093E66" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @elseif($value->item_key === 'flexibility')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <path d="M45 35L55 45M55 45L45 55M55 45H39.8975C38.2589 44.9833 36.6495 44.5642 35.211 43.7795C33.7724 42.9948 32.5487 41.8686 31.6475 40.5L30.75 39.375M45 5L55 15M55 15L45 25M55 15L40.0675 15C38.4513 14.989 36.8565 15.3698 35.4196 16.11C33.9828 16.8502 32.7468 17.9276 31.8175 19.25L18.1825 40.75C17.2532 42.0724 16.0172 43.1498 14.5804 43.89C13.1435 44.6302 11.5487 45.011 9.9325 45H5M5 15H9.93C11.7936 14.987 13.6238 15.4951 15.2141 16.4669C16.8043 17.4388 18.0914 18.8356 18.93 20.5" stroke="#093E66" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @elseif($value->item_key === 'commitment')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <path d="M27.5 42.5L32.5 47.5C32.9925 47.9925 33.5771 48.3831 34.2205 48.6496C34.8639 48.9162 35.5536 49.0533 36.25 49.0533C36.9464 49.0533 37.6361 48.9162 38.2795 48.6496C38.9229 48.3831 39.5075 47.9925 40 47.5C40.4925 47.0076 40.8831 46.4229 41.1496 45.7795C41.4161 45.1361 41.5533 44.4465 41.5533 43.75C41.5533 43.0536 41.4161 42.364 41.1496 41.7206C40.8831 41.0771 40.4925 40.4925 40 40M35 35L41.25 41.25C42.2446 42.2446 43.5935 42.8033 45 42.8033C46.4065 42.8033 47.7554 42.2446 48.75 41.25C49.7446 40.2555 50.3033 38.9066 50.3033 37.5C50.3033 36.0935 49.7446 34.7446 48.75 33.75L39.05 24.05C37.6437 22.6455 35.7375 21.8566 33.75 21.8566C31.7625 21.8566 29.8563 22.6455 28.45 24.05L26.25 26.25C25.2554 27.2446 23.9065 27.8033 22.5 27.8033C21.0935 27.8033 19.7446 27.2446 18.75 26.25C17.7554 25.2555 17.1967 23.9066 17.1967 22.5C17.1967 21.0935 17.7554 19.7446 18.75 18.75L25.775 11.725C28.0556 9.4504 31.0297 8.00142 34.2266 7.60747C37.4235 7.21352 40.6604 7.89712 43.425 9.55004L44.6 10.25C45.6645 10.8925 46.9301 11.1153 48.15 10.875L52.5 10M52.5 7.50004L55 35H50M7.5 7.50004L5 35L21.25 51.25C22.2446 52.2446 23.5935 52.8033 25 52.8033C26.4065 52.8033 27.7554 52.2446 28.75 51.25C29.7446 50.2555 30.3033 48.9066 30.3033 47.5C30.3033 46.0935 29.7446 44.7446 28.75 43.75M7.5 10H27.5" stroke="#093E66" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @elseif($value->item_key === 'teamwork')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <path d="M40 52.5V47.5C40 44.8478 38.9464 42.3043 37.0711 40.4289C35.1957 38.5536 32.6522 37.5 30 37.5H15C12.3478 37.5 9.8043 38.5536 7.92893 40.4289C6.05357 42.3043 5 44.8478 5 47.5V52.5M40 7.82C42.1444 8.37592 44.0435 9.62816 45.3992 11.3802C46.7549 13.1322 47.4905 15.2847 47.4905 17.5C47.4905 19.7153 46.7549 21.8678 45.3992 23.6198C44.0435 25.3718 42.1444 26.6241 40 27.18M55 52.5V47.5C54.9983 45.2843 54.2609 43.1319 52.9034 41.3808C51.5459 39.6296 49.6453 38.3789 47.5 37.825M32.5 17.5C32.5 23.0228 28.0228 27.5 22.5 27.5C16.9772 27.5 12.5 23.0228 12.5 17.5C12.5 11.9772 16.9772 7.5 22.5 7.5C28.0228 7.5 32.5 11.9772 32.5 17.5Z" stroke="#093E66" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @elseif($value->item_key === 'human-value')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <path d="M48.535 36.035C52.5 32.07 55 28.75 55 23.75C54.9999 20.968 54.156 18.2515 52.5797 15.9592C51.0033 13.6669 48.7687 11.9066 46.171 10.911C43.5732 9.91534 40.7346 9.7311 38.03 10.3826C35.3253 11.0341 32.8819 12.4907 31.0225 14.56M48.535 36.035C48.0419 36.5282 47.4565 36.9194 46.8122 37.1862C46.1679 37.4531 45.4774 37.5905 44.78 37.5905C44.0826 37.5905 43.3921 37.4531 42.7478 37.1862C42.1035 36.9194 41.5181 36.5282 41.025 36.035C41.5589 36.5177 41.989 37.104 42.2893 37.7581C42.5896 38.4123 42.7538 39.1206 42.7719 39.8402C42.79 40.5597 42.6616 41.2754 42.3945 41.9438C42.1274 42.6122 41.7272 43.2193 41.2183 43.7283C40.7093 44.2372 40.1022 44.6374 39.4338 44.9045C38.7654 45.1715 38.0497 45.2999 37.3302 45.2819C36.6106 45.2638 35.9023 45.0996 35.2481 44.7993C34.594 44.499 34.0078 44.0689 33.525 43.535C34.0185 44.0266 34.4103 44.6106 34.6779 45.2537C34.9455 45.8968 35.0837 46.5864 35.0847 47.2829C35.0856 47.9795 34.9492 48.6694 34.6833 49.3132C34.4174 49.957 34.0272 50.5421 33.535 51.035C33.0599 51.5102 32.494 51.8848 31.8709 52.1363C31.2478 52.3879 30.5805 52.5113 29.9086 52.4992C29.2368 52.4871 28.5743 52.3396 27.9607 52.0657C27.3472 51.7918 26.7951 51.397 26.3375 50.905L12.5 37.5C8.75 33.75 5 29.5 5 23.75C5.00056 20.9682 5.84485 18.252 7.4214 15.9601C8.99794 13.6682 11.2326 11.9084 13.8302 10.913C16.4278 9.91771 19.2662 9.73368 21.9706 10.3852C24.675 11.0368 27.1182 12.4934 28.9775 14.5625C29.2555 14.8208 29.621 14.9642 30.0005 14.9638C30.38 14.9633 30.7452 14.819 31.0225 14.56M48.535 36.035C49.4724 35.0974 49.9989 33.8258 49.9989 32.5C49.9989 31.1742 49.4724 29.9026 48.535 28.965L43.8325 24.26C43.273 23.7002 42.6087 23.2562 41.8775 22.9533C41.1464 22.6503 40.3627 22.4944 39.5712 22.4944C38.7798 22.4944 37.9961 22.6503 37.265 22.9533C36.5338 23.2562 35.8695 23.7002 35.31 24.26L31.035 28.535C30.0974 29.4723 28.8258 29.9989 27.5 29.9989C26.1742 29.9989 24.9026 29.4723 23.965 28.535C23.0276 27.5974 22.5011 26.3258 22.5011 25C22.5011 23.6742 23.0276 22.4026 23.965 21.465L31.0225 14.56" stroke="#093E66" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @endif
                            </div>
                            <h3>{!! nl2br(e((string) $value->title)) !!}</h3>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($memberships && $membershipItems->isNotEmpty())
        <section class="about-page__memberships">
            <div class="container">
                <h2 class="about-page__section-title">{{ $memberships->title ?: 'Pertenecemos a' }}</h2>
                <div class="about-memberships__grid">
                    @foreach($membershipItems as $item)
                        <article class="about-memberships__item about-memberships__item--{{ $item->item_key }}">
                            <img src="{{ media_asset_url($item->media) }}" alt="{{ $item->title ?? 'Afiliación' }}">
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('web.about._footer')
@endsection
