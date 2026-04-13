<section class="hc-section hc-equipments">
    <div class="hc-container">

        @if($section?->title)
        <h2 class="hc-section__title">{{ $section->title }}</h2>
        @elseif($section === null && $equipments->count())
        {{-- no CMS title, render silently --}}
        @endif

        <div class="hc-card-grid hc-equipments__grid">
            @forelse($equipments as $eq)
            <div class="hc-card hc-equipment-card">
                <div class="hc-card__img-wrap hc-equipment-card__img-wrap">
                    <img src="{{ $eq->main_image_url }}" alt="{{ $eq->title }}" loading="lazy">
                </div>
                <div class="hc-card__body hc-equipment-card__body">
                    <svg class="hc-equipment-card__divider" xmlns="http://www.w3.org/2000/svg" width="307" height="1" viewBox="0 0 307 1" fill="none" aria-hidden="true">
                        <path d="M0 0.5L307 0.500027" stroke="#C5C5C5"/>
                    </svg>
                    <h3 class="hc-card__title hc-equipment-card__title">{{ $eq->title }}</h3>
                    <a href="{{ route('equipos.show', $eq->slug ?: $eq->id) }}" class="hc-equipment-card__btn">
                        <span class="hc-equipment-card__btn-text">Ver más</span>
                        <svg class="hc-equipment-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5.83325 5.8335H14.1666M14.1666 5.8335V14.1668M14.1666 5.8335L5.83325 14.1668" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <p class="hc-empty">No hay equipos disponibles.</p>
            @endforelse
        </div>

    </div>
</section>
