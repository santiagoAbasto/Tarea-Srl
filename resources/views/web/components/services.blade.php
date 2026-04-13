<section class="hc-section hc-services">
    <div class="hc-container">

        @if($section?->title)
        <h2 class="hc-services__title">{{ $section->title }}</h2>
        @endif

        <div class="hc-card-grid hc-card-grid--4 hc-services__grid">
            @forelse($services as $service)
            <div class="hc-card hc-service-card{{ $service->slug === 'soldaduras' ? ' hc-service-card--soldaduras' : '' }}">
                <div class="hc-card__img-wrap hc-service-card__img-wrap">
                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}" loading="lazy">
                </div>
                <div class="hc-card__body hc-service-card__body">
                    <h3 class="hc-card__title hc-service-card__title">{{ $service->title }}</h3>
                    @if($service->subtitle)
                    <p class="hc-card__subtitle hc-service-card__subtitle">{{ $service->subtitle }}</p>
                    @endif
                    <a href="{{ route('servicios.show', $service->slug) }}" class="hc-service-card__btn">
                        <span class="hc-service-card__btn-text">Ver todas</span>
                        <svg class="hc-service-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5.83325 5.83325H14.1666M14.1666 5.83325V14.1666M14.1666 5.83325L5.83325 14.1666" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <p class="hc-empty">No hay servicios disponibles.</p>
            @endforelse
        </div>

        @if($services->count())
        <div class="hc-services__footer">
            <a href="{{ route('servicios.index') }}" class="hc-services__all-btn">
                <span class="hc-services__all-btn-text">Ver todos</span>
                <svg class="hc-services__all-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5.83325 5.83325H14.1666M14.1666 5.83325V14.1666M14.1666 5.83325L5.83325 14.1666" stroke="#D50101" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        @endif

    </div>
</section>
