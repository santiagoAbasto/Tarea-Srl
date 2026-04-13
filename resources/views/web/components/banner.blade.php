@php
$bannerTitle = strip_tags($section?->title ?? '');
$bannerDesc  = cms_rich_text($section?->description) ?? '';
$bannerTag   = strip_tags($section?->subtitle ?? '');
$bannerBtn   = $section?->meta['button_text'] ?? 'Consultar';
$bannerUrl   = $section?->meta['button_url']  ?? route('contacto');
$bannerMedia = $section?->activeMedia->first();
@endphp

<section class="hc-banner"
    @if($bannerMedia) style="background-image: url('{{ $bannerMedia->file_url }}')" @endif>

    <div class="hc-banner__overlay"></div>

    <div class="hc-banner__content">
            @if($bannerTag)
            <span class="hc-banner__tag">{{ $bannerTag }}</span>
            @endif

            @if($bannerTitle)
            <h2 class="hc-banner__title">{{ $bannerTitle }}</h2>
            @endif

            @if($bannerDesc)
            <div class="hc-banner__desc">{!! $bannerDesc !!}</div>
            @endif

            <a href="{{ $bannerUrl }}" class="hc-banner__btn">
                <span class="hc-banner__btn-text">{{ $bannerBtn }}</span>
                <svg class="hc-banner__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5.83325 5.83325H14.1666M14.1666 5.83325V14.1666M14.1666 5.83325L5.83325 14.1666" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
    </div>

</section>
