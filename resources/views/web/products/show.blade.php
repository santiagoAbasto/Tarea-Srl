@extends('web.layouts.app')

@section('title', $product->name.' | '.config('app.name'))
@section('meta_description', $product->short_description ?: $product->description ?: $product->material ?: $product->observations)
@section('meta_og_image', $selectedMedia ? media_asset_url($selectedMedia) : ($product->mainMedia ? media_asset_url($product->mainMedia) : ''))

@php
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : ($catalogHeaderLogoUrl ?? $headerLogoUrl ?? null);
@endphp

@section('content')
    <section class="products-catalog-page">
        <header class="site-header site-header--catalog">
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
            <div class="products-catalog-page__crumbs-inner products-catalog-page__crumbs-inner--wide">
                <span>
                    <a href="{{ route('web.home') }}">Inicio</a>
                    &gt;
                    <a href="{{ route('web.products.index') }}">Productos</a>
                    &gt;
                    <a href="{{ route('web.products.family', $selectedFamily->slug) }}">{{ $selectedFamily->name }}</a>
                    &gt;
                    <span class="is-current">{{ $product->name }}</span>
                </span>
            </div>
        </div>
    </section>

    <section class="products-show">
        <div class="container products-show__layout">
            <aside class="products-catalog__sidebar">
                <a
                    href="{{ route('web.products.family', $selectedFamily->slug) }}"
                    class="products-catalog__sidebar-link {{ !$selectedSubfamily ? 'is-active' : '' }}"
                >
                    <span class="products-catalog__sidebar-label">Todos los productos</span>
                </a>

                @foreach($sidebarItems as $item)
                    <a href="{{ route('web.products.family', $selectedFamily->slug) }}?subfamily={{ $item->slug }}" class="products-catalog__sidebar-link {{ $item->slug === ($selectedSubfamily->slug ?? null) ? 'is-active' : '' }}" style="--accent-color: {{ $item->accent_color ?: '#8BCFDE' }};">
                        <span class="products-catalog__sidebar-accent"></span>
                        <span class="products-catalog__sidebar-label">{{ $item->name }}</span>
                    </a>
                @endforeach
            </aside>

            <div class="products-show__content">
                <div class="products-show__hero">
                    <div class="products-show__gallery">
                        <div class="products-show__main-image">
                            @if($selectedMedia)
                                <img src="{{ media_asset_url($selectedMedia) }}" alt="{{ $selectedMedia->alt_text ?: $product->name }}">
                            @endif
                        </div>

                        @if($gallery->count() > 0)
                            <div class="products-show__thumbs">
                                @foreach($gallery as $media)
                                    <a href="{{ route('web.products.show', [$selectedFamily->slug, $product->slug]) }}?media={{ $media->id }}" class="products-show__thumb {{ $selectedMedia && $selectedMedia->id === $media->id ? 'is-active' : '' }}">
                                        <img src="{{ media_asset_url($media) }}" alt="{{ $media->alt_text ?: $product->name }}">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="products-show__summary">
                        @php
                            $eyebrowColor = match($product->subfamily?->slug) {
                                'elementos-de-cadena' => '#8BCFDE',
                                'componentes-hilo-guardia-pat' => '#A46DA9',
                                'preensamblado' => '#52B6AC',
                                'rienda' => '#E86428',
                                'red-compacta' => '#9DC66C',
                                'montajes-de-aisladores' => '#E85556',
                                default => ($product->subfamily?->accent_color ?: '#8BCFDE'),
                            };
                        @endphp

                        <span class="products-show__eyebrow" style="color: {{ $eyebrowColor }};">
                            {{ $product->subfamily?->name ?? $product->family?->name }}
                        </span>
                        <h1>{{ $product->name }}</h1>
                        <div class="products-show__divider" aria-hidden="true"></div>

                        <div class="products-show__details">
                            @if($product->material)
                                <div class="products-show__detail">
                                    <strong>Material:</strong>
                                    <p>{{ $product->material }}</p>
                                </div>
                            @endif

                            @if($product->treatment)
                                <div class="products-show__detail">
                                    <strong>Tratamiento:</strong>
                                    <p>{{ $product->treatment }}</p>
                                </div>
                            @endif

                            @if($product->observations)
                                <div class="products-show__detail">
                                    <strong>Observaciones:</strong>
                                    <p>{{ $product->observations }}</p>
                                </div>
                            @endif

                            @if(!$product->material && !$product->treatment && !$product->observations && $product->description)
                                <div class="products-show__detail">
                                    <p>{{ $product->description }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="products-show__actions">
                            <a href="{{ route('web.products.technical-sheet', [$selectedFamily->slug, $product->slug]) }}" class="products-show__button products-show__button--outline" target="_blank" rel="noopener"><span>Ficha técnica</span></a>
                            <a href="{{ route('web.quote.show', ['product' => $product->id]) }}" class="products-show__button products-show__button--solid"><span>Consultar</span></a>
                        </div>
                    </div>
                </div>

                @if($specTable && $specTable->columns->isNotEmpty() && $specTable->rows->isNotEmpty())
                    <div class="products-show__specs">
                        @php
                            $columns = $specTable->columns->values();
                            $isGroupedSpecsHeader =
                                $columns->count() >= 11
                                && $columns->get(0)?->label === 'CÓDIGO'
                                && str_starts_with($columns->get(1)?->label ?? '', 'RANGO ')
                                && str_starts_with($columns->get(2)?->label ?? '', 'RANGO ')
                                && str_starts_with($columns->get(3)?->label ?? '', 'øCABLE ')
                                && str_starts_with($columns->get(4)?->label ?? '', 'øCABLE ');
                        @endphp

                        <table class="products-show__table">
                            <thead>
                                @if($isGroupedSpecsHeader)
                                    <tr class="products-show__table-head-row products-show__table-head-row--top">
                                        <th rowspan="2">
                                            <span>{{ $columns[0]->label }}</span>
                                        </th>
                                        <th colspan="2">
                                            <span>RANGO [{{ $columns[1]->unit }}]</span>
                                        </th>
                                        <th colspan="2">
                                            <span>øCABLE [{{ $columns[3]->unit }}]</span>
                                        </th>
                                        @foreach($columns->slice(5) as $column)
                                            <th rowspan="2">
                                                <span>{{ $column->label }}</span>
                                                @if($column->unit)
                                                    <small>[{{ $column->unit }}]</small>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr class="products-show__table-head-row products-show__table-head-row--bottom">
                                        <th>
                                            <span>{{ str_replace('RANGO ', '', $columns[1]->label) }}</span>
                                        </th>
                                        <th>
                                            <span>{{ str_replace('RANGO ', '', $columns[2]->label) }}</span>
                                        </th>
                                        <th>
                                            <span>{{ str_replace('øCABLE ', '', $columns[3]->label) }}</span>
                                        </th>
                                        <th>
                                            <span>{{ str_replace('øCABLE ', '', $columns[4]->label) }}</span>
                                        </th>
                                    </tr>
                                @else
                                    <tr class="products-show__table-head-row">
                                        @foreach($specTable->columns as $column)
                                            <th>
                                                <span>{{ $column->label }}</span>
                                                @if($column->unit)
                                                    <small>[{{ $column->unit }}]</small>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @foreach($specTable->rows as $row)
                                    @php
                                        $valuesByColumn = $row->values->keyBy('product_spec_column_id');
                                    @endphp
                                    <tr>
                                        @foreach($specTable->columns as $column)
                                            <td>{{ $valuesByColumn->get($column->id)?->value }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($relatedProducts->isNotEmpty())
                    <div class="products-show__related">
                        <h2>Productos relacionados</h2>
                        <div class="products-show__related-grid">
                            @foreach($relatedProducts as $relatedProduct)
                                @php
                                    $relatedEyebrowColor = match($relatedProduct->subfamily?->slug) {
                                        'elementos-de-cadena' => '#8BCFDE',
                                        'componentes-hilo-guardia-pat' => '#A46DA9',
                                        'preensamblado' => '#52B6AC',
                                        'rienda' => '#E86428',
                                        'red-compacta' => '#9DC66C',
                                        'montajes-de-aisladores' => '#E85556',
                                        default => ($relatedProduct->subfamily?->accent_color ?: '#8BCFDE'),
                                    };
                                @endphp
                                <a href="{{ route('web.products.show', [$selectedFamily->slug, $relatedProduct->slug]) }}" class="products-show__related-link">
                                    <article class="products-show__related-card">
                                        <div class="products-show__related-media">
                                            <span class="products-show__related-plus" aria-hidden="true"></span>
                                            @if($relatedProduct->mainMedia)
                                                <img src="{{ media_asset_url($relatedProduct->mainMedia) }}" alt="{{ $relatedProduct->name }}">
                                            @endif
                                        </div>
                                        <div class="products-show__related-body">
                                            <span class="products-show__related-eyebrow" style="color: {{ $relatedEyebrowColor }};">
                                                {{ $relatedProduct->subfamily?->name ?? $relatedProduct->family?->name }}
                                            </span>
                                            <h3>{{ $relatedProduct->name }}</h3>
                                        </div>
                                    </article>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @include('web.about._footer')
@endsection
