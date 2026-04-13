@extends('web.layouts.app')

@section('title', 'Presupuesto | '.config('app.name'))

@php
    $heroImage = $hero?->media ? media_asset_url($hero->media) : null;
    $darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
    $darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
@endphp

@section('content')
    <section class="page-hero quote-page-hero">
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
                <a href="{{ route('web.search.index') }}" class="nav-mobile__search" aria-label="Ir a búsqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Buscar</span>
                </a>
                <a href="{{ route('web.quote.show') }}" class="nav-mobile__cta is-active">Presupuesto</a>
            </nav>
        </div>
        <div id="navOverlay" class="nav-overlay" aria-hidden="true"></div>

        @if($heroImage)
            <div class="page-hero__media">
                <img src="{{ $heroImage }}" alt="{{ $hero?->title ?? 'Presupuesto' }}">
            </div>
        @endif

        <div class="page-hero__overlay"></div>

        <div class="container page-hero__content">
            <div class="page-hero__crumbs">
                <span><a href="{{ route('web.home') }}">Inicio</a> &gt; <span class="is-current">Presupuesto</span></span>
            </div>
            <h1>{{ $hero?->title ?? 'SOLICITUD DE PRESUPUESTO' }}</h1>
        </div>
    </section>

    <section class="quote-page">
        <div class="container">
            @if($errors->any())
                <div class="quote-page__notice quote-page__notice--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('web.quote.store') }}" method="POST" enctype="multipart/form-data" class="quote-page__form">
                @csrf
                <input
                    type="text"
                    name="{{ config('security.forms.honeypot_field', 'website') }}"
                    value=""
                    tabindex="-1"
                    autocomplete="off"
                    aria-hidden="true"
                    style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;"
                >

                <div class="quote-page__section quote-page__section--personal">
                    <h2>{{ $content?->title ?? 'Datos personales' }}</h2>
                    <div class="quote-page__personal-grid">
                        <label class="quote-page__field">
                            <span>{{ $fields?->get('name_label')?->field_value ?? 'Nombre y Apellido*' }}</span>
                            <input type="text" name="name" value="{{ old('name') }}" required>
                        </label>

                        <label class="quote-page__field">
                            <span>{{ $fields?->get('email_label')?->field_value ?? 'Email*' }}</span>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                        </label>

                        <label class="quote-page__field">
                            <span>{{ $fields?->get('company_label')?->field_value ?? 'Empresa' }}</span>
                            <input type="text" name="company" value="{{ old('company') }}">
                        </label>

                        <label class="quote-page__field">
                            <span>{{ $fields?->get('phone_label')?->field_value ?? 'Teléfono*' }}</span>
                            <input type="text" name="phone" value="{{ old('phone') }}">
                        </label>
                    </div>
                </div>

                <div class="quote-page__divider"></div>

                <div class="quote-page__section quote-page__section--selected">
                    <h2>{{ $content?->subtitle ?? 'Productos seleccionados' }}</h2>

                    @if($selectedProducts->isNotEmpty())
                        <div class="quote-page__products">
                            @foreach($selectedProducts as $index => $product)
                                <div class="quote-page__product-row">
                                    <div class="quote-page__product-meta">
                                        <div class="quote-page__product-thumb">
                                            @if($product->mainMedia)
                                                <img src="{{ media_asset_url($product->mainMedia) }}" alt="{{ $product->name }}">
                                            @endif
                                        </div>
                                        <div class="quote-page__product-copy">
                                            <strong>{{ $product->name }}</strong>
                                        </div>
                                    </div>

                                    <label class="quote-page__quantity">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                        <input type="hidden" name="items[{{ $index }}][product_name]" value="{{ $product->name }}">
                                        <input type="number" name="items[{{ $index }}][quantity]" min="1" value="{{ old("items.$index.quantity", 1) }}" required>
                                        <span>Cantidad</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="quote-page__empty">
                            No hay productos seleccionados por el momento. Podés volver al catálogo y usar el botón <strong>Consultar</strong> desde un producto.
                        </div>
                    @endif
                </div>

                <div class="quote-page__divider"></div>

                <div class="quote-page__bottom">
                    <label class="quote-page__field quote-page__field--message">
                        <span>{{ $fields?->get('message_label')?->field_value ?? 'Mensaje' }}</span>
                        <textarea name="message" placeholder="Dejanos un mensaje">{{ old('message') }}</textarea>
                    </label>

                    <div class="quote-page__files">
                        <label class="quote-page__field">
                            <span>Adjuntar archivo</span>
                            <span class="quote-page__file-list" data-file-list>
                                <span class="quote-page__file-row">
                                    <span class="quote-page__file-input">
                                        <input type="file" name="attachments[]" data-file-input>
                                        <span class="quote-page__file-placeholder" data-file-placeholder>Selecciona archivo*</span>
                                    </span>
                                </span>
                            </span>
                        </label>

                        <button type="button" class="quote-page__attach-more" data-add-file>+ ADJUNTAR OTRO</button>

                        <div class="quote-page__actions">
                            <span>*Datos obligatorios</span>
                            <button type="submit" class="quote-page__submit">
                                <span>{{ $fields?->get('button_text')?->field_value ?? 'Enviar solicitud' }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <g opacity="0.5">
                                        <path d="M12 3V15M12 3L17 8M12 3L7 8M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="#1A181C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @include('web.about._footer')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                }, 7000);
            };

            const fileList = document.querySelector('[data-file-list]');
            const addFileButton = document.querySelector('[data-add-file]');

            function bindFileRow(row) {
                const input = row.querySelector('[data-file-input]');
                const placeholder = row.querySelector('[data-file-placeholder]');

                if (!input || !placeholder) {
                    return;
                }

                input.addEventListener('change', function () {
                    const fileName = this.files && this.files.length ? this.files[0].name : 'Selecciona archivo*';
                    placeholder.textContent = fileName;
                    placeholder.classList.toggle('is-filled', fileName !== 'Selecciona archivo*');
                });
            }

            if (fileList && addFileButton) {
                fileList.querySelectorAll('.quote-page__file-row').forEach(bindFileRow);

                addFileButton.addEventListener('click', function () {
                    const row = document.createElement('span');
                    row.className = 'quote-page__file-row';
                    row.innerHTML = `
                        <span class="quote-page__file-input">
                            <input type="file" name="attachments[]" data-file-input>
                            <span class="quote-page__file-placeholder" data-file-placeholder>Selecciona archivo*</span>
                        </span>
                    `;

                    fileList.appendChild(row);
                    bindFileRow(row);
                });
            }

            @if(session('quote_success'))
            toast(@json(session('quote_success')), 'success');
            @endif

            @if($selectedProducts->isNotEmpty())
            window.setTimeout(function () {
                const url = new URL(window.location.href);
                url.searchParams.set('clear_selection', '1');
                window.location.replace(url.toString());
            }, {{ max(1, (int) $selectionTtlSeconds) * 1000 }});
            @endif
        });
    </script>
@endpush
