@extends('web.layouts.app')

@php
$banner = $page?->sections->where('section_key', 'servicios_banner')->first();
$bannerMeta = $banner?->meta ?? [];
$detailButtonText = $bannerMeta['detail_button_text'] ?? null;
$detailButtonUrl = $bannerMeta['detail_button_url'] ?? null;
@endphp

@section('content')
<section class="servicios-detail">
    <div class="hc-container">
        <div class="servicios-detail__grid">
            <div class="servicios-detail__img-wrap">
                <img src="{{ $service->image_url }}" alt="{{ $service->title }}" class="servicios-detail__img">
            </div>
            <div class="servicios-detail__content">
                <h1 class="servicios-detail__title">{{ $service->title }}</h1>
                @if($service->subtitle)
                <p class="servicios-detail__subtitle">{{ $service->subtitle }}</p>
                @endif
                @if($service->description)
                <div class="servicios-detail__desc">{!! cms_rich_text($service->description) !!}</div>
                @endif
                <a href="{{ route('presupuesto', ['context' => 'service', 'slug' => $service->slug]) }}" class="servicios-detail__btn">
                    <span class="servicios-detail__btn-text">{{ $detailButtonText }}</span>
                    <svg class="servicios-detail__btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5.8335 5.83337H14.1668M14.1668 5.83337V14.1667M14.1668 5.83337L5.8335 14.1667" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
