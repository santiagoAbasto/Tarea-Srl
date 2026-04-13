@extends('web.layouts.app')

@php
    $firstProduct = $products->first();
    $selectedProductId = $selectedProductId ?? $firstProduct?->id;
    $hasProductTabs = $products->isNotEmpty();
    $displayItems = $hasProductTabs ? $products : collect([$equipment]);
@endphp

@section('content')
<section class="equipos-detail">
    <div class="hc-container">
        @if($hasProductTabs)
        <div class="equipos-detail__tabs" role="tablist" aria-label="{{ $equipment->title }}">
            @foreach($products as $product)
            <button
                type="button"
                class="equipos-detail__tab{{ $product->id === $selectedProductId ? ' is-active' : '' }}"
                data-equipo-tab="{{ $product->id }}"
                role="tab"
                aria-selected="{{ $product->id === $selectedProductId ? 'true' : 'false' }}">
                {{ $product->title }}
            </button>
            @endforeach
        </div>

        <svg class="equipos-detail__tabs-divider" xmlns="http://www.w3.org/2000/svg" width="1224" height="1" viewBox="0 0 1224 1" fill="none" aria-hidden="true">
            <path d="M0 0.5L1224 0.499893" stroke="#C5C5C5"/>
        </svg>
        @endif

        <div class="equipos-detail__panels">
            @foreach($displayItems as $product)
            <section
                class="equipos-detail__panel{{ ! $hasProductTabs || $product->id === $selectedProductId ? ' is-active' : '' }}"
                @if($hasProductTabs)
                data-equipo-panel="{{ $product->id }}"
                @endif
                role="tabpanel">
                <div class="equipos-detail__grid">
                    <div class="equipos-detail__img-wrap">
                        <img src="{{ $product->main_image_url }}" alt="{{ $product->title }}" class="equipos-detail__img">
                    </div>

                    <div class="equipos-detail__content">
                        @if($product->description)
                        @php
                            $sanitizedDescription = cms_rich_text($product->description);
                        @endphp
                        <div class="equipos-detail__desc">{!! $sanitizedDescription !!}</div>
                        @endif

                        @if(!empty($product->features))
                        <ul class="equipos-detail__features">
                            @foreach($product->features as $feature)
                            <li class="equipos-detail__feature">
                                <svg class="equipos-detail__feature-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <g clip-path="url(#equiposFeatureClip{{ $product->id }}{{ $loop->index }})">
                                        <path d="M7.49984 10.0001L9.1665 11.6667L12.4998 8.33341M18.3332 10.0001C18.3332 14.6025 14.6022 18.3334 9.99984 18.3334C5.39746 18.3334 1.6665 14.6025 1.6665 10.0001C1.6665 5.39771 5.39746 1.66675 9.99984 1.66675C14.6022 1.66675 18.3332 5.39771 18.3332 10.0001Z" stroke="#AA0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                        <clipPath id="equiposFeatureClip{{ $product->id }}{{ $loop->index }}">
                                            <rect width="20" height="20" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span class="equipos-detail__feature-text">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        <a href="{{ route('presupuesto', ['context' => 'equipment', 'slug' => $product->slug ?: $product->id]) }}" class="equipos-detail__btn">
                            <span class="equipos-detail__btn-text">Pedir presupuesto</span>
                            <svg class="equipos-detail__btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5.8335 5.83337H14.1668M14.1668 5.83337V14.1667M14.1668 5.83337L5.8335 14.1667" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
            @endforeach
        </div>
    </div>
</section>

<script>
(() => {
  const tabs = document.querySelectorAll('[data-equipo-tab]');
  const panels = document.querySelectorAll('[data-equipo-panel]');
  if (!tabs.length || !panels.length) return;

  const activate = (id) => {
    tabs.forEach((tab) => {
      const active = tab.dataset.equipoTab === id;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    panels.forEach((panel) => {
      panel.classList.toggle('is-active', panel.dataset.equipoPanel === id);
    });
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => activate(tab.dataset.equipoTab));
  });
})();
</script>
@endsection
