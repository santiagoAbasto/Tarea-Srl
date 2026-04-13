@extends('web.layouts.app')

@section('title', 'Contacto | '.config('app.name'))

@php
$heroImage = $hero?->media ? media_asset_url($hero->media) : null;
$darkLogo = \App\Models\MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();
$darkLogoUrl = $darkLogo ? media_asset_url($darkLogo) : $headerLogoUrl;
$mapIframe = cms_map_iframe($settings?->map_iframe) ?: '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3276.0352701280444!2d-58.4384772!3d-34.8050545!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bcd393e0e97a97%3A0x22dafa76979e211f!2sTarea%20Sociedad%20de%20Responsabilidad%20Limitada!5e0!3m2!1ses!2sar!4v1775757906240!5m2!1ses!2sar" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
$addressText = (string) ($settings?->address ?: $footer?->contact_address ?: '');
$phonePrimaryText = (string) ($settings?->phone_primary ?: $footer?->phone_primary ?: '');
$phoneSecondaryText = (string) ($settings?->phone_secondary ?: $footer?->phone_secondary ?: '');
$emailPrimaryText = (string) ($settings?->email_primary ?: $footer?->email_primary ?: '');
$mapLink = (string) ($settings?->map_link ?: 'https://maps.app.goo.gl/FFiEn78PLBZWdKep8');
@endphp

@section('content')
<section class="page-hero contact-page-hero">
    <header class="site-header" id="mainNav">
        <div class="container site-header__inner">
            <a href="{{ route('web.home') }}" class="brand">
                @if($headerLogo)
                <img
                    src="{{ $headerLogoUrl }}"
                    alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                    class="brand__logo">
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
                <a href="{{ route('web.contact.show') }}" class="is-active">Contacto</a>
                <span class="nav__search" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                        <path d="M15.5 15.5L11.8809 11.8809M11.8809 11.8809C12.4999 11.2618 12.991 10.5269 13.326 9.71802C13.6611 8.90917 13.8335 8.04225 13.8335 7.16676C13.8335 6.29127 13.6611 5.42435 13.326 4.6155C12.991 3.80665 12.4999 3.07172 11.8809 2.45265C11.2618 1.83358 10.5269 1.34251 9.71802 1.00748C8.90917 0.672441 8.04225 0.5 7.16676 0.5C6.29127 0.5 5.42435 0.672441 4.6155 1.00748C3.80665 1.34251 3.07172 1.83358 2.45265 2.45265C1.20239 3.70291 0.5 5.39863 0.5 7.16676C0.5 8.9349 1.20239 10.6306 2.45265 11.8809C3.70291 13.1311 5.39863 13.8335 7.16676 13.8335C8.9349 13.8335 10.6306 13.1311 11.8809 11.8809Z" stroke-linecap="round" stroke-linejoin="round" />
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
                    class="nav-mobile__logo">
                @elseif($headerLogo)
                <img
                    src="{{ $headerLogoUrl }}"
                    alt="{{ $headerLogo->alt_text ?: ($footer?->brand_name ?? 'Tarea SRL') }}"
                    class="nav-mobile__logo">
                @else
                <span>{{ strtoupper($footer?->brand_name ?? 'TAREA') }}</span>
                @endif
            </a>
            <button id="navClose" type="button" class="nav-mobile__close" aria-label="Cerrar menú">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
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
            <a href="{{ route('web.contact.show') }}" class="is-active">Contacto</a>
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
        <img src="{{ $heroImage }}" alt="{{ $hero?->title ?? 'Contacto' }}">
    </div>
    @endif

    <div class="page-hero__overlay"></div>

    <div class="container page-hero__content">
        <div class="page-hero__crumbs">
            <span><a href="{{ route('web.home') }}">Inicio</a> &gt; <span class="is-current">Contacto</span></span>
        </div>
        <h1>{{ $hero?->title ?? 'CONTACTO' }}</h1>
    </div>
</section>

<section class="contact-page">
    <div class="container">
        <div class="contact-page__panel">
            <div class="contact-page__grid">
                <aside class="contact-page__info">
                    <p class="contact-page__intro">
                        Si desea realizar una consulta complete el siguiente formulario y nos pondremos en contacto a la brevedad
                    </p>

                    @if($addressText !== '')
                    <a href="{{ $mapLink }}" class="contact-page__line" target="_blank" rel="noopener noreferrer">
                        <span class="contact-page__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M16.6668 8.33333C16.6668 13.3333 10.0002 18.3333 10.0002 18.3333C10.0002 18.3333 3.3335 13.3333 3.3335 8.33333C3.3335 6.56522 4.03588 4.86953 5.28612 3.61929C6.53636 2.36904 8.23205 1.66666 10.0002 1.66666C11.7683 1.66666 13.464 2.36904 14.7142 3.61929C15.9645 4.86953 16.6668 6.56522 16.6668 8.33333Z" stroke="#093E66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10 10.8333C11.3807 10.8333 12.5 9.71405 12.5 8.33334C12.5 6.95262 11.3807 5.83334 10 5.83334C8.61929 5.83334 7.5 6.95262 7.5 8.33334C7.5 9.71405 8.61929 10.8333 10 10.8333Z" stroke="#093E66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span>{{ $addressText }}</span>
                    </a>
                    @endif

                    @if($phonePrimaryText !== '')
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phonePrimaryText) }}" class="contact-page__line">
                        <span class="contact-page__icon contact-page__icon--phone">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <g clip-path="url(#clip0_5614_1540)">
                                    <path d="M18.3332 14.1V16.6C18.3341 16.8321 18.2866 17.0618 18.1936 17.2745C18.1006 17.4871 17.9643 17.678 17.7933 17.8349C17.6222 17.9918 17.4203 18.1112 17.2005 18.1856C16.9806 18.2599 16.7477 18.2876 16.5165 18.2667C13.9522 17.988 11.489 17.1118 9.32486 15.7083C7.31139 14.4289 5.60431 12.7218 4.32486 10.7083C2.91651 8.53434 2.04007 6.05916 1.76653 3.48333C1.7457 3.25289 1.77309 3.02063 1.84695 2.80135C1.9208 2.58207 2.03951 2.38057 2.1955 2.20968C2.3515 2.03879 2.54137 1.90226 2.75302 1.80877C2.96468 1.71528 3.19348 1.66688 3.42486 1.66667H5.92486C6.32928 1.66269 6.72136 1.8059 7.028 2.06961C7.33464 2.33332 7.53493 2.69954 7.59153 3.1C7.69705 3.90006 7.89274 4.68561 8.17486 5.44167C8.28698 5.73994 8.31125 6.0641 8.24478 6.37573C8.17832 6.68737 8.02392 6.97343 7.79986 7.2L6.74153 8.25833C7.92783 10.3446 9.65524 12.072 11.7415 13.2583L12.7999 12.2C13.0264 11.9759 13.3125 11.8215 13.6241 11.7551C13.9358 11.6886 14.2599 11.7129 14.5582 11.825C15.3143 12.1071 16.0998 12.3028 16.8999 12.4083C17.3047 12.4654 17.6744 12.6693 17.9386 12.9812C18.2029 13.2932 18.3433 13.6913 18.3332 14.1Z" stroke="#093E66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_5614_1540">
                                        <rect width="20" height="20" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </span>
                        <span>{{ $phonePrimaryText }}</span>
                    </a>
                    @endif

                    @if($phoneSecondaryText !== '')
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phoneSecondaryText) }}" class="contact-page__line" target="_blank" rel="noopener noreferrer">
                        <span class="contact-page__icon contact-page__icon--whatsapp">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5823 11.985C14.3328 11.8608 13.1095 11.2625 12.8817 11.1792C12.6539 11.0967 12.4881 11.0558 12.3215 11.3042C12.1557 11.5508 11.6793 12.1092 11.5344 12.2742C11.3887 12.44 11.2438 12.46 10.9952 12.3367C10.7465 12.2117 9.94429 11.9508 8.99391 11.1075C8.25453 10.4509 7.75464 9.64002 7.60978 9.39169C7.46492 9.14419 7.59387 9.01002 7.71863 8.88669C7.83084 8.77585 7.96732 8.59752 8.09209 8.45335C8.21685 8.30835 8.25788 8.20502 8.34078 8.03919C8.42451 7.87419 8.38265 7.73002 8.31985 7.60585C8.25788 7.48169 7.7605 6.26252 7.55284 5.76669C7.35104 5.28419 7.14589 5.35003 6.99349 5.34169C6.8478 5.33503 6.682 5.33336 6.51621 5.33336C6.35041 5.33336 6.08079 5.39503 5.85303 5.64336C5.62444 5.89086 4.98219 6.49002 4.98219 7.70919C4.98219 8.92752 5.87313 10.105 5.99789 10.2709C6.12266 10.4359 7.75213 12.9375 10.2482 14.01C10.8428 14.265 11.3058 14.4175 11.6667 14.5308C12.2629 14.72 12.8055 14.6933 13.2342 14.6292C13.7115 14.5583 14.7063 14.03 14.9139 13.4517C15.1208 12.8733 15.1207 12.3775 15.0588 12.2742C14.9968 12.1708 14.831 12.1092 14.5815 11.985H14.5823ZM10.0423 18.1542H10.0389C8.55634 18.1544 7.10099 17.7578 5.8254 17.0058L5.52396 16.8275L2.39062 17.6458L3.22712 14.6058L3.03035 14.2942C2.20149 12.9811 1.76286 11.4615 1.76512 9.91085C1.76679 5.36919 5.47958 1.6742 10.0456 1.6742C12.2562 1.6742 14.3345 2.53253 15.897 4.08919C16.6676 4.85301 17.2785 5.76133 17.6941 6.7616C18.1098 7.76188 18.322 8.83425 18.3186 9.91668C18.3169 14.4583 14.6041 18.1542 10.0423 18.1542ZM17.086 2.9067C16.1634 1.98247 15.0657 1.24965 13.8564 0.7507C12.6472 0.251754 11.3505 -0.00339687 10.0414 3.41479e-05C4.55347 3.41479e-05 0.085409 4.44586 0.0837344 9.91002C0.0811914 11.649 0.539563 13.3578 1.4126 14.8642L0 20L5.27861 18.6217C6.73884 19.4134 8.37519 19.8283 10.0381 19.8283H10.0423C15.5302 19.8283 19.9983 15.3825 20 9.91752C20.004 8.61525 19.7485 7.32511 19.2484 6.12172C18.7482 4.91833 18.0132 3.82559 17.086 2.9067Z" fill="#093E66" />
                            </svg>
                        </span>
                        <span>{{ $phoneSecondaryText }}</span>
                    </a>
                    @endif

                    @if($emailPrimaryText !== '')
                    <a href="mailto:{{ $emailPrimaryText }}" class="contact-page__line">
                        <span class="contact-page__icon contact-page__icon--mail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M16.6665 3.33333H3.33317C2.4127 3.33333 1.6665 4.07952 1.6665 5V15C1.6665 15.9205 2.4127 16.6667 3.33317 16.6667H16.6665C17.587 16.6667 18.3332 15.9205 18.3332 15V5C18.3332 4.07952 17.587 3.33333 16.6665 3.33333Z" stroke="#093E66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M18.3332 5.83333L10.8582 10.5833C10.6009 10.7445 10.3034 10.83 9.99984 10.83C9.69624 10.83 9.39878 10.7445 9.1415 10.5833L1.6665 5.83333" stroke="#093E66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span>{{ $emailPrimaryText }}</span>
                    </a>
                    @endif
                </aside>

                <form action="{{ route('web.contact.store') }}" method="POST" class="contact-page__form">
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
                    <div class="contact-page__form-grid">
                        <label class="contact-page__field">
                            <span>Nombre*</span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Juan" required>
                        </label>
                        <label class="contact-page__field">
                            <span>Apellido*</span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Perez" required>
                        </label>
                        <label class="contact-page__field">
                            <span>Email*</span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="juanperez@gmail.com" required>
                        </label>
                        <label class="contact-page__field">
                            <span>Celular</span>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="11 1234 5678">
                        </label>
                        <label class="contact-page__field contact-page__field--message">
                            <span>Mensaje</span>
                            <textarea name="message" placeholder="Dejanos un mensaje">{{ old('message') }}</textarea>
                        </label>
                        <div class="contact-page__actions">
                            <span>*Datos obligatorios</span>
                            <button type="submit" class="contact-page__submit">Enviar mensaje</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="contact-page__map">
                {!! $mapIframe !!}
            </div>
        </div>
    </div>
</section>

@include('web.about._footer')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
        let root = document.getElementById('ios-toast-root');
        if (!root) {
            root = document.createElement('div');
            root.id = 'ios-toast-root';
            root.className = 'ios-toast-root';
            document.body.appendChild(root);
        }
        const node = document.createElement('div');
        node.className = 'ios-toast ios-toast--success is-visible';
        node.textContent = @json(session('success'));
        root.appendChild(node);
        setTimeout(() => {
            node.classList.remove('is-visible');
            setTimeout(() => node.remove(), 250);
        }, 7000);
        @endif
    });
</script>
@endpush
