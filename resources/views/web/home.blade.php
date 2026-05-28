@extends('web.layouts.app')

@section('title', 'Inicio | '.config('app.name'))

@php
    $slideCount = $slides->count();
    $representantes = $sections->get('representantes');
    $productosHome = $sections->get('productos_home');
    $about = $sections->get('about_preview');
    $featured = $sections->get('featured_products');
    $news = $sections->get('latest_news');
    $trusted = $sections->get('trusted_clients');
    $clientChunks = $clients->chunk(6)->values();
    $headerLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();
    $headerLogoUrl = $headerLogo ? media_asset_url($headerLogo) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : null;
    $isRichText = static fn ($value) => preg_match('/<[^>]+>/', (string) $value) === 1;
@endphp

@section('content')
    <section class="hero">
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
                    <a href="{{ route('web.news.index') }}">Novedades</a>
                    <a href="{{ route('web.contact.show') }}">Contacto</a>
                    <span class="nav__search" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                            <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <a href="{{ route('web.quote.show') }}" class="nav__cta">Presupuesto</a>
                </nav>

                {{-- Botón hamburguesa — visible solo en mobile --}}
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

        <div class="hero__media">
            @foreach($slides as $heroSlide)
                @php
                    $desktopMediaUrl = media_asset_url($heroSlide->desktopMedia);
                    $mobileMediaUrl = media_asset_url($heroSlide->mobileMedia) ?: $desktopMediaUrl;
                    $desktopYoutubeUrl = $heroSlide->desktopYoutubeEmbedUrl();
                    $mobileYoutubeUrl = $heroSlide->mobileYoutubeEmbedUrl() ?: $desktopYoutubeUrl;
                    $desktopMimeType = $heroSlide->desktopMedia?->mime_type ?? 'video/mp4';
                    $mobileMimeType = $heroSlide->mobileMedia?->mime_type ?? $desktopMimeType;
                @endphp
                <div
                    class="hero-slide {{ $loop->first ? 'active' : '' }}"
                    data-autoplay-seconds="{{ $heroSlide->autoplay_override_seconds ?: 6 }}"
                >
                    @if($heroSlide->media_type === 'youtube' && ($desktopYoutubeUrl || $mobileYoutubeUrl))
                        <iframe
                            src=""
                            data-desktop-src="{{ $desktopYoutubeUrl }}"
                            data-mobile-src="{{ $mobileYoutubeUrl }}"
                            title="{{ $heroSlide->alt_text ?: $heroSlide->title ?: 'Video de inicio' }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    @elseif($heroSlide->media_type === 'video' && ($desktopMediaUrl || $mobileMediaUrl))
                        <video
                            autoplay
                            muted
                            loop
                            playsinline
                            webkit-playsinline="true"
                            preload="metadata"
                            data-desktop-src="{{ $desktopMediaUrl }}"
                            data-mobile-src="{{ $mobileMediaUrl }}"
                            data-desktop-type="{{ $desktopMimeType }}"
                            data-mobile-type="{{ $mobileMimeType }}"
                        >
                            <source src="" type="{{ $desktopMimeType }}">
                        </video>
                    @elseif($desktopMediaUrl || $mobileMediaUrl)
                        @if($mobileMediaUrl && $mobileMediaUrl !== $desktopMediaUrl)
                            <picture>
                                <source media="(max-width: 768px)" srcset="{{ $mobileMediaUrl }}">
                                <img src="{{ $desktopMediaUrl ?: $mobileMediaUrl }}" alt="{{ $heroSlide->alt_text ?: $heroSlide->title }}">
                            </picture>
                        @else
                            <img src="{{ $desktopMediaUrl ?: $mobileMediaUrl }}" alt="{{ $heroSlide->alt_text ?: $heroSlide->title }}">
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
        <div class="hero__overlay"></div>

        <div class="container hero__content">
            <div class="hero__copy-stack">
                @forelse($slides as $heroSlide)
                    <div class="hero__copy hero-content {{ $loop->first ? 'active' : '' }}">
                        <h1>{!! nl2br(e($heroSlide->title ?? 'INNOVACIÓN Y CRECIMIENTO')) !!}</h1>
                        <p>{{ $heroSlide->description }}</p>
                        @if($heroSlide->button_url)
                            <a href="{{ $heroSlide->button_url }}" class="hero__button">{{ $heroSlide->button_text }}</a>
                        @endif
                    </div>
                @empty
                    <div class="hero__copy hero-content active">
                        <h1>INNOVACIÓN Y CRECIMIENTO</h1>
                        <p>Diseñamos sistemas, procesos y productos para generar soluciones innovadoras que respondan a las necesidades de nuestros clientes.</p>
                    </div>
                @endforelse

                @if($slideCount > 1)
                    <div class="hero__dots">
                        @foreach($slides as $dotSlide)
                            <button type="button" class="hero-dot {{ $loop->first ? 'active' : '' }}" aria-label="Ir al slide {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================
         Panel de navegación mobile (visible solo en ≤768px)
    ============================================================ --}}
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

    @if($representantes && $partners->isNotEmpty())
        <section class="section section--representatives">
            <div class="container">
                <div class="logos-row logos-row--representatives">
                    <h2 class="section__title section__title--flush">{{ $representantes->title }}</h2>
                    <div class="representatives-track">
                        @foreach($partners as $partner)
                            @php
                                $logoClass = match($loop->index) {
                                    0 => 'representative-logo representative-logo--maurizio',
                                    1 => 'representative-logo representative-logo--cst',
                                    2 => 'representative-logo representative-logo--entec',
                                    3 => 'representative-logo representative-logo--sao-jose',
                                    default => 'representative-logo',
                                };
                            @endphp
                            <a href="{{ $partner->website_url ?: '#' }}" class="{{ $logoClass }}">
                                <img src="{{ media_asset_url($partner->logo) }}" alt="{{ $partner->name }}">
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($about)
        <section class="section section--about-preview">
            <div class="container">
                <div class="about-block">
                    <div class="about-block__media">
                        <img src="{{ media_asset_url($about->media) }}" alt="{{ $about->title }}">
                    </div>
                    <div class="about-block__content">
                        <h2>{{ $about->title }}</h2>
                        <div class="about-block__text">
                            @if($isRichText($about->description))
                                {!! cms_rich_text($about->description) !!}
                            @else
                                @foreach(explode("\n\n", (string) $about->description) as $paragraph)
                                    @if(trim($paragraph) !== '')
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        @if($about->button_url)
                            <a href="{{ $about->button_url }}">{{ $about->button_text }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($productosHome && $product_families->isNotEmpty())
        <section class="section section--home-products">
            <div class="container">
                <div class="section__header">
                    <h2 class="section__title">{{ $productosHome->title }}</h2>
                    @if($productosHome->button_url)
                        <a href="{{ $productosHome->button_url }}" class="section__button">{{ $productosHome->button_text }}</a>
                    @endif
                </div>
                <div class="cards-3 cards-3--home-products">
                    @foreach($product_families as $family)
                        <a href="{{ route('web.products.family', $family->slug) }}" class="product-card product-card--link">
                            <div class="product-card__media">
                                <span class="product-card__plus" aria-hidden="true"></span>
                                <img src="{{ media_asset_url($family->coverMedia) }}" alt="{{ $family->name }}">
                            </div>
                            <div class="product-card__body" style="--accent-color: {{ $family->accent_color ?: '#8BCFDE' }};">
                                <h3 class="product-title">{{ $family->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($featured && $featured_products->isNotEmpty())
        <section class="section section--featured-products">
            <div class="container">
                <div class="section__header">
                    <h2 class="section__title">{{ $featured->title }}</h2>
                </div>
                <div class="cards-4 cards-4--featured-products">
                    @foreach($featured_products as $product)
                        <a href="{{ route('web.products.show', [$product->family?->slug ?? 'todos-los-productos', $product->slug]) }}" class="product-tile product-tile--link">
                            <div class="product-tile__media {{ $product->brandLogoMedia ? 'product-tile__media--with-brand' : '' }}">
                                <span class="product-tile__plus" aria-hidden="true"></span>
                                <div class="product-tile__image-wrap">
                                    <img src="{{ media_asset_url($product->mainMedia) }}" alt="{{ $product->name }}">
                                </div>
                                @if($product->brandLogoMedia)
                                    <span class="product-tile__brand-logo" aria-hidden="true">
                                        <img
                                            src="{{ media_asset_url($product->brandLogoMedia) }}"
                                            alt=""
                                        >
                                    </span>
                                @endif
                            </div>
                            <div class="product-tile__body">
                                @php
                                    $eyebrowClass = match($product->subfamily?->slug) {
                                        'elementos-de-cadena' => 'eyebrow--chain',
                                        'preensamblado' => 'eyebrow--preassembled',
                                        'rienda' => 'eyebrow--rienda',
                                        'red-compacta' => 'eyebrow--compact',
                                        'montajes-de-aisladores' => 'eyebrow--insulators',
                                        'componentes-hilo-guardia-pat' => 'eyebrow--pat',
                                        default => '',
                                    };

                                    $nameClass = match($product->subfamily?->slug) {
                                        'componentes-hilo-guardia-pat' => 'featured-product__name--pat',
                                        default => '',
                                    };
                                @endphp
                                <div class="eyebrow {{ $eyebrowClass }}">
                                    {{ strtoupper($product->subfamily?->name ?? $product->family?->name ?? '') }}
                                </div>
                                <h3 class="featured-product__name {{ $nameClass }}">{{ $product->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($news && $posts->isNotEmpty())
        <section class="section section--latest-news">
            <div class="container">
                <div class="section__header">
                    <h2 class="section__title">{{ $news->title }}</h2>
                    @if($news->button_url)
                        <a href="{{ $news->button_url }}" class="section__button">{{ $news->button_text }}</a>
                    @endif
                </div>
                <div class="cards-3 cards-3--latest-news">
                    @foreach($posts->take(3) as $post)
                        <article class="news-card">
                            <div class="news-card__media">
                                <img src="{{ media_asset_url($post->coverMedia) }}" alt="{{ $post->title }}">
                            </div>
                            <div class="news-card__body">
                                <div class="eyebrow eyebrow--news">{{ strtoupper($post->category?->name ?? 'Novedad') }}</div>
                                <h3>{{ $post->title }}</h3>
                                <p>{{ $post->excerpt }}</p>
                                <a href="{{ route('web.news.show', $post->slug) }}" class="news-card__more">Leer más</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if($posts->count() > 3)
                    <div class="carousel-dots carousel-dots--news">
                        <button type="button" class="is-active" aria-label="Ir a la novedad 1"></button>
                        <button type="button" aria-label="Ir a la novedad 2"></button>
                        <button type="button" aria-label="Ir a la novedad 3"></button>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($trusted && $clients->isNotEmpty())
        <section class="section section--muted section--trusted-clients {{ $clients->count() > 6 ? 'section--trusted-clients--with-dots' : 'section--trusted-clients--without-dots' }}">
            <div class="container">
                <div class="section__header">
                    <h2 class="section__title">{{ $trusted->title }}</h2>
                    @if($trusted->button_url)
                        <a href="{{ $trusted->button_url }}" class="section__button">VER TODOS</a>
                    @endif
                </div>
                <div class="clients-carousel" data-clients-carousel>
                    @foreach($clientChunks as $chunk)
                        <div class="cards-4 cards-4--clients clients-carousel__page {{ $loop->first ? 'is-active' : '' }}" data-clients-page>
                            @foreach($chunk as $client)
                                <div class="client-logo-card">
                                    <img src="{{ media_asset_url($client->logo) }}" alt="{{ $client->name }}" class="client-logo-card__image">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @if($clients->count() > 6)
                    <div class="carousel-dots carousel-dots--clients">
                        @foreach($clientChunks as $chunk)
                            <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" aria-label="Ir al bloque de clientes {{ $loop->iteration }}" data-clients-dot data-index="{{ $loop->index }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    @php
                        $footerBrandLogo = \App\Models\MediaAsset::query()
                            ->where('path', 'uploads/branding/logoheader.png')
                            ->first();
                    @endphp
                    <a href="{{ route('web.home') }}" class="brand footer__brand">
                        @if($footerBrandLogo)
                            <img
                                src="{{ media_asset_url($footerBrandLogo) }}"
                                alt="{{ $footerBrandLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                                class="brand__logo"
                            >
                        @else
                            <span>{{ strtoupper($footer?->brand_name ?? 'Tarea') }}</span>
                        @endif
                    </a>
                </div>
                <div>
                    <h3 class="footer__title">Secciones</h3>
                    <div class="footer__links">
                        <a href="{{ route('web.about') }}">Nosotros</a>
                        <a href="{{ route('web.products.index') }}">Productos</a>
                        <a href="{{ route('web.catalog.show') }}">Catálogo</a>
                        <a href="{{ route('web.quality') }}">Calidad</a>
                        <a href="{{ route('web.clients.index') }}">Clientes</a>
                        <a href="{{ route('web.partners.index') }}">Representadas</a>
                        <a href="{{ route('web.news.index') }}">Novedades</a>
                        <a href="{{ route('web.quote.show') }}">Presupuesto</a>
                        <a href="{{ route('web.contact.show') }}">Contacto</a>
                    </div>
                </div>
                <div>
                    <h3 class="footer__title">{{ $footer?->newsletter_title }}</h3>
                    <form class="footer__newsletter js-newsletter-form" method="POST" action="{{ route('web.newsletter.store') }}">
                        @csrf
                        <input type="email" name="email" placeholder="{{ $footer?->newsletter_placeholder }}" required>
                        <button type="submit" aria-label="Enviar newsletter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                    <h3 class="footer__title footer__title--social">Redes sociales</h3>
                    <div class="footer__social">
                        <div>
                            @foreach($social_links as $social)
                                @php
                                    $platform = strtolower((string) $social->platform);
                                    $socialUrl = $social->url;
                                @endphp
                                <a href="{{ $socialUrl }}" class="footer__social-link" aria-label="{{ $social->platform }}" target="_blank" rel="noopener noreferrer">
                                    @if(str_contains($platform, 'instagram'))
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <g clip-path="url(#clip0_5757_325_home)">
                                                <path d="M14.5837 5.41675H14.592M5.83366 1.66675H14.167C16.4682 1.66675 18.3337 3.53223 18.3337 5.83341V14.1667C18.3337 16.4679 16.4682 18.3334 14.167 18.3334H5.83366C3.53247 18.3334 1.66699 16.4679 1.66699 14.1667V5.83341C1.66699 3.53223 3.53247 1.66675 5.83366 1.66675ZM13.3337 9.47508C13.4365 10.1686 13.318 10.8769 12.9951 11.4993C12.6722 12.1216 12.1613 12.6263 11.535 12.9415C10.9087 13.2567 10.199 13.3664 9.50681 13.255C8.8146 13.1436 8.17513 12.8168 7.67936 12.321C7.18359 11.8253 6.85677 11.1858 6.74538 10.4936C6.63399 9.80137 6.74371 9.09166 7.05893 8.46539C7.37415 7.83913 7.87881 7.3282 8.50115 7.00528C9.12348 6.68237 9.83179 6.5639 10.5253 6.66675C11.2328 6.77165 11.8877 7.1013 12.3934 7.607C12.8991 8.11271 13.2288 8.76765 13.3337 9.47508Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_5757_325_home">
                                                    <rect width="20" height="20" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    @elseif(str_contains($platform, 'facebook'))
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M14.9997 1.66675H12.4997C11.3946 1.66675 10.3348 2.10573 9.5534 2.88714C8.77199 3.66854 8.33301 4.72835 8.33301 5.83341V8.33341H5.83301V11.6667H8.33301V18.3334H11.6663V11.6667H14.1663L14.9997 8.33341H11.6663V5.83341C11.6663 5.6124 11.7541 5.40044 11.9104 5.24416C12.0667 5.08788 12.2787 5.00008 12.4997 5.00008H14.9997V1.66675Z" fill="white"/>
                                        </svg>
                                    @elseif(str_contains($platform, 'youtube'))
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M2.08309 14.1667C1.50095 11.4194 1.50095 8.58061 2.08309 5.83333C2.15959 5.55434 2.30738 5.30006 2.51193 5.09551C2.71649 4.89095 2.97077 4.74316 3.24976 4.66667C7.7193 3.92622 12.2802 3.92622 16.7498 4.66667C17.0288 4.74316 17.283 4.89095 17.4876 5.09551C17.6921 5.30006 17.8399 5.55434 17.9164 5.83333C18.4986 8.58061 18.4986 11.4194 17.9164 14.1667C17.8399 14.4457 17.6921 14.6999 17.4876 14.9045C17.283 15.1091 17.0288 15.2568 16.7498 15.3333C12.2802 16.0739 7.71929 16.0739 3.24976 15.3333C2.97077 15.2568 2.71649 15.1091 2.51193 14.9045C2.30738 14.6999 2.15959 14.4457 2.08309 14.1667Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.3331 12.5L12.4998 10L8.3331 7.5V12.5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @elseif(str_contains($platform, 'linkedin'))
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 19" fill="none">
                                            <path d="M12.6663 6.33325C13.9261 6.33325 15.1343 6.8337 16.0251 7.72449C16.9159 8.61529 17.4163 9.82347 17.4163 11.0833V16.6249H14.2497V11.0833C14.2497 10.6633 14.0829 10.2606 13.7859 9.96367C13.489 9.66673 13.0863 9.49992 12.6663 9.49992C12.2464 9.49992 11.8437 9.66673 11.5468 9.96367C11.2498 10.2606 11.083 10.6633 11.083 11.0833V16.6249H7.91634V11.0833C7.91634 9.82347 8.41679 8.61529 9.30758 7.72449C10.1984 6.8337 11.4066 6.33325 12.6663 6.33325Z" fill="white"/>
                                            <path d="M4.74967 7.12492H1.58301V16.6249H4.74967V7.12492Z" fill="white"/>
                                            <path d="M3.16634 4.74992C4.04079 4.74992 4.74967 4.04104 4.74967 3.16659C4.74967 2.29213 4.04079 1.58325 3.16634 1.58325C2.29189 1.58325 1.58301 2.29213 1.58301 3.16659C1.58301 4.04104 2.29189 4.74992 3.16634 4.74992Z" fill="white"/>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="footer__title">{{ $footer?->contact_title }}</h3>
                    @php
                        $addressText = (string) ($footer?->contact_address ?? '');
                        $phonePrimaryText = (string) ($footer?->phone_primary ?? '');
                        $phoneSecondaryText = (string) ($footer?->phone_secondary ?? '');
                        $emailText = (string) ($footer?->email_primary ?? '');
                        $phonePrimaryHref = preg_replace('/[^0-9+]/', '', $phonePrimaryText);
                        $phoneSecondaryDigits = preg_replace('/[^0-9]/', '', $phoneSecondaryText);
                        $footerWhatsappUrl = $footer?->whatsapp_url ?: 'https://wa.me/5491134411550';
                    @endphp
                    <div class="footer__contact">
                        <a href="https://maps.app.goo.gl/FFiEn78PLBZWdKep8" target="_blank" rel="noopener noreferrer">
                            <span class="footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M16.6663 8.33341C16.6663 13.3334 9.99967 18.3334 9.99967 18.3334C9.99967 18.3334 3.33301 13.3334 3.33301 8.33341C3.33301 6.5653 4.03539 4.86961 5.28563 3.61937C6.53587 2.36913 8.23156 1.66675 9.99967 1.66675C11.7678 1.66675 13.4635 2.36913 14.7137 3.61937C15.964 4.86961 16.6663 6.5653 16.6663 8.33341Z" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 10.8333C11.3807 10.8333 12.5 9.71396 12.5 8.33325C12.5 6.95254 11.3807 5.83325 10 5.83325C8.61929 5.83325 7.5 6.95254 7.5 8.33325C7.5 9.71396 8.61929 10.8333 10 10.8333Z" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>{{ $addressText }}</span>
                        </a>
                        <a href="tel:{{ $phonePrimaryHref }}">
                            <span class="footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <g clip-path="url(#clip0_5757_299_home_contact)">
                                        <path d="M18.3332 14.0999V16.5999C18.3341 16.832 18.2866 17.0617 18.1936 17.2744C18.1006 17.487 17.9643 17.6779 17.7933 17.8348C17.6222 17.9917 17.4203 18.1112 17.2005 18.1855C16.9806 18.2599 16.7477 18.2875 16.5165 18.2666C13.9522 17.988 11.489 17.1117 9.32486 15.7083C7.31139 14.4288 5.60431 12.7217 4.32486 10.7083C2.91651 8.53426 2.04007 6.05908 1.76653 3.48325C1.7457 3.25281 1.77309 3.02055 1.84695 2.80127C1.9208 2.58199 2.03951 2.38049 2.1955 2.2096C2.3515 2.03871 2.54137 1.90218 2.75302 1.80869C2.96468 1.7152 3.19348 1.6668 3.42486 1.66658H5.92486C6.32928 1.6626 6.72136 1.80582 7.028 2.06953C7.33464 2.33324 7.53493 2.69946 7.59153 3.09992C7.69705 3.89997 7.89274 4.68552 8.17486 5.44158C8.28698 5.73985 8.31125 6.06401 8.24478 6.37565C8.17832 6.68729 8.02392 6.97334 7.79986 7.19992L6.74153 8.25825C7.92783 10.3445 9.65524 12.072 11.7415 13.2583L12.7999 12.1999C13.0264 11.9759 13.3125 11.8215 13.6241 11.755C13.9358 11.6885 14.2599 11.7128 14.5582 11.8249C15.3143 12.107 16.0998 12.3027 16.8999 12.4083C17.3047 12.4654 17.6744 12.6693 17.9386 12.9812C18.2029 13.2931 18.3433 13.6912 18.3332 14.0999Z" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5757_299_home_contact">
                                            <rect width="20" height="20" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                            <span>{{ $phonePrimaryText }}</span>
                        </a>
                        <a href="https://wa.me/{{ $phoneSecondaryDigits }}" target="_blank" rel="noopener noreferrer">
                            <span class="footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5823 11.985C14.3328 11.8608 13.1095 11.2625 12.8817 11.1792C12.6539 11.0967 12.4881 11.0558 12.3215 11.3042C12.1557 11.5508 11.6793 12.1092 11.5344 12.2742C11.3887 12.44 11.2438 12.46 10.9952 12.3367C10.7465 12.2117 9.94429 11.9508 8.99391 11.1075C8.25453 10.4509 7.75464 9.64002 7.60978 9.39169C7.46492 9.14419 7.59387 9.01002 7.71863 8.88669C7.83084 8.77585 7.96732 8.59752 8.09209 8.45335C8.21685 8.30835 8.25788 8.20502 8.34078 8.03919C8.42451 7.87419 8.38265 7.73002 8.31985 7.60585C8.25788 7.48169 7.7605 6.26252 7.55284 5.76669C7.35104 5.28419 7.14589 5.35003 6.99349 5.34169C6.8478 5.33503 6.682 5.33336 6.51621 5.33336C6.35041 5.33336 6.08079 5.39503 5.85303 5.64336C5.62444 5.89086 4.98219 6.49002 4.98219 7.70919C4.98219 8.92752 5.87313 10.105 5.99789 10.2709C6.12266 10.4359 7.75213 12.9375 10.2482 14.01C10.8428 14.265 11.3058 14.4175 11.6667 14.5308C12.2629 14.72 12.8055 14.6933 13.2342 14.6292C13.7115 14.5583 14.7063 14.03 14.9139 13.4517C15.1208 12.8733 15.1207 12.3775 15.0588 12.2742C14.9968 12.1708 14.831 12.1092 14.5815 11.985H14.5823ZM10.0423 18.1542H10.0389C8.55634 18.1544 7.10099 17.7578 5.8254 17.0058L5.52396 16.8275L2.39062 17.6458L3.22712 14.6058L3.03035 14.2942C2.20149 12.9811 1.76286 11.4615 1.76512 9.91085C1.76679 5.36919 5.47958 1.6742 10.0456 1.6742C12.2562 1.6742 14.3345 2.53253 15.897 4.08919C16.6676 4.85301 17.2785 5.76133 17.6941 6.7616C18.1098 7.76188 18.322 8.83425 18.3186 9.91668C18.3169 14.4583 14.6041 18.1542 10.0423 18.1542ZM17.086 2.9067C16.1634 1.98247 15.0657 1.24965 13.8564 0.7507C12.6472 0.251754 11.3505 -0.00339687 10.0414 3.41479e-05C4.55347 3.41479e-05 0.085409 4.44586 0.0837344 9.91002C0.0811914 11.649 0.539563 13.3578 1.4126 14.8642L0 20L5.27861 18.6217C6.73884 19.4134 8.37519 19.8283 10.0381 19.8283H10.0423C15.5302 19.8283 19.9983 15.3825 20 9.91752C20.004 8.61525 19.7485 7.32511 19.2484 6.12172C18.7482 4.91833 18.0132 3.82559 17.086 2.9067Z" fill="#8FD8F8"/>
                                </svg>
                            </span>
                            <span>{{ $phoneSecondaryText }}</span>
                        </a>
                        <a href="mailto:{{ $emailText }}">
                            <span class="footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M16.667 3.33325H3.33366C2.41318 3.33325 1.66699 4.07944 1.66699 4.99992V14.9999C1.66699 15.9204 2.41318 16.6666 3.33366 16.6666H16.667C17.5875 16.6666 18.3337 15.9204 18.3337 14.9999V4.99992C18.3337 4.07944 17.5875 3.33325 16.667 3.33325Z" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.3337 5.83325L10.8587 10.5833C10.6014 10.7444 10.3039 10.8299 10.0003 10.8299C9.69673 10.8299 9.39927 10.7444 9.14199 10.5833L1.66699 5.83325" stroke="#8FD8F8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>{{ $emailText }}</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
        <div class="footer__bottom">
            <div class="container footer__bottom-inner">
                <span>{{ $footer?->copyright_text ?: '© Copyright 2026 Tarea S.R.L. Todos los derechos reservados' }}</span>
                <span aria-hidden="true"></span>
                <span class="footer__byline">By <a href="https://osole.com.ar/" target="_blank" rel="noopener noreferrer" class="footer__osole">Osole</a></span>
            </div>
        </div>
        @if($footerWhatsappUrl)
            <a href="{{ $footerWhatsappUrl }}" class="footer__whatsapp-float" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M23.3317 19.176C22.9325 18.9774 20.9751 18.02 20.6107 17.8867C20.2463 17.7547 19.981 17.6894 19.7144 18.0867C19.4491 18.4814 18.6868 19.3747 18.4551 19.6387C18.2219 19.904 17.9902 19.936 17.5923 19.7387C17.1943 19.5387 15.9109 19.1214 14.3903 17.772C13.2073 16.7214 12.4074 15.424 12.1756 15.0267C11.9439 14.6307 12.1502 14.416 12.3498 14.2187C12.5293 14.0414 12.7477 13.756 12.9473 13.5254C13.147 13.2934 13.2126 13.128 13.3452 12.8627C13.4792 12.5987 13.4122 12.368 13.3118 12.1694C13.2126 11.9707 12.4168 10.02 12.0845 9.22671C11.7617 8.45471 11.4334 8.56004 11.1896 8.54671C10.9565 8.53604 10.6912 8.53337 10.4259 8.53337C10.1607 8.53337 9.72926 8.63204 9.36485 9.02937C8.9991 9.42537 7.97151 10.384 7.97151 12.3347C7.97151 14.284 9.39701 16.168 9.59663 16.4334C9.79625 16.6974 12.4034 20.7 16.3972 22.416C17.3484 22.824 18.0893 23.068 18.6667 23.2493C19.6206 23.552 20.4888 23.5093 21.1747 23.4067C21.9384 23.2933 23.53 22.448 23.8623 21.5227C24.1932 20.5974 24.1932 19.804 24.0941 19.6387C23.9949 19.4734 23.7296 19.3747 23.3304 19.176H23.3317ZM16.0676 29.0467H16.0623C13.6901 29.0471 11.3616 28.4125 9.32064 27.2093L8.83833 26.924L3.82499 28.2333L5.1634 23.3693L4.84855 22.8707C3.52239 20.7698 2.82057 18.3384 2.82419 15.8574C2.82687 8.59071 8.76732 2.67872 16.073 2.67872C19.6099 2.67872 22.9352 4.05205 25.4352 6.54271C26.6682 7.76482 27.6456 9.21813 28.3106 10.8186C28.9757 12.419 29.3153 14.1348 29.3097 15.8667C29.307 23.1333 23.3666 29.0467 16.0676 29.0467ZM27.3376 4.65071C25.8614 3.17195 24.1051 1.99943 22.1703 1.20112C20.2355 0.402806 18.1608 -0.00543499 16.0663 5.46366e-05C7.28556 5.46366e-05 0.136654 7.11338 0.133975 15.856C0.129906 18.6384 0.863301 21.3725 2.26016 23.7827L0 32L8.44578 29.7947C10.7821 31.0615 13.4003 31.7253 16.0609 31.7253H16.0676C24.8483 31.7253 31.9972 24.612 31.9999 15.868C32.0064 13.7844 31.5977 11.7202 30.7974 9.79475C29.9971 7.86933 28.8212 6.12094 27.3376 4.65071Z" fill="white"/>
                </svg>
            </a>
        @endif
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const clientPages = Array.from(document.querySelectorAll('[data-clients-page]'));
            const clientDots = Array.from(document.querySelectorAll('[data-clients-dot]'));

            if (!clientPages.length || !clientDots.length) {
                return;
            }

            const activateClientPage = (index) => {
                clientPages.forEach((page, pageIndex) => {
                    page.classList.toggle('is-active', pageIndex === index);
                });

                clientDots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === index);
                });
            };

            clientDots.forEach((dot, index) => {
                dot.addEventListener('click', () => activateClientPage(index));
            });

            const toast = (message, type = 'success') => {
                let root = document.getElementById('ios-toast-root');
                if (!root) {
                    root = document.createElement('div');
                    root.id = 'ios-toast-root';
                    root.className = 'ios-toast-root';
                    document.body.appendChild(root);
                }
                const node = document.createElement('div');
                node.className = `ios-toast ios-toast--${type}`;
                node.textContent = message;
                root.appendChild(node);
                requestAnimationFrame(() => node.classList.add('is-visible'));
                setTimeout(() => {
                    node.classList.remove('is-visible');
                    setTimeout(() => node.remove(), 250);
                }, 2200);
            };

            document.querySelectorAll('.js-newsletter-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const button = form.querySelector('button[type="submit"]');
                    const data = new FormData(form);
                    button.disabled = true;
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: data,
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload.message || 'No se pudo enviar.');
                        }
                        toast(payload.message || 'Suscripcion exitosa.', 'success');
                        form.reset();
                    } catch (error) {
                        toast(error.message || 'Error al suscribirse.', 'error');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        });
    </script>
@endsection
