@extends('web.layouts.app')

@section('title', $q ? 'Búsqueda: ' . $q : 'Buscar')
@section('meta_description', 'Resultados de búsqueda de servicios, equipos y trabajos realizados.')

@section('content')
<section class="hc-search-page">
    <div class="hc-container">
        <div class="hc-search-page__head">
            <h1 class="hc-search-page__title">Buscar</h1>
            <form action="{{ route('search') }}" method="GET" class="hc-search-page__form" role="search">
                <div class="hc-search-page__field">
                    <svg class="hc-search-page__field-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M17.5 17.5L13.8833 13.8833M15.8333 9.16667C15.8333 12.8486 12.8486 15.8333 9.16667 15.8333C5.48477 15.8333 2.5 12.8486 2.5 9.16667C2.5 5.48477 5.48477 2.5 9.16667 2.5C12.8486 2.5 15.8333 5.48477 15.8333 9.16667Z" stroke="#202020" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Buscar servicios, equipos o trabajos realizados" autocomplete="off">
                </div>
                <button type="submit" class="hc-search-page__submit">Buscar</button>
            </form>
            <p class="hc-search-page__meta">
                @if($q !== '')
                    {{ $totalResults }} resultado{{ $totalResults === 1 ? '' : 's' }} para “{{ $q }}”
                @else
                    Escribí una palabra clave para buscar en toda la web.
                @endif
            </p>
        </div>

        @if($q !== '' && $totalResults === 0)
            <div class="hc-search-page__empty">
                <p>No encontramos coincidencias para “{{ $q }}”.</p>
            </div>
        @endif

        @if($services->isNotEmpty())
        <section class="hc-search-page__group">
            <div class="hc-search-page__group-head">
                <h2 class="hc-search-page__group-title">Servicios</h2>
                <span class="hc-search-page__group-count">{{ $services->count() }}</span>
            </div>
            <div class="servicios-page__grid">
                @foreach($services as $service)
                <article class="servicios-card{{ $service->subtitle ? ' servicios-card--with-subtitle' : '' }}{{ $service->slug === 'soldaduras' ? ' servicios-card--soldaduras' : '' }}">
                    <div class="servicios-card__img-wrap">
                        <img src="{{ $service->image_url }}" alt="{{ $service->title }}" loading="lazy">
                    </div>
                    <div class="servicios-card__divider" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="229" height="1" viewBox="0 0 229 1" fill="none">
                            <path d="M0 0.5L229 0.50002" stroke="#C5C5C5"/>
                        </svg>
                    </div>
                    <div class="servicios-card__body">
                        <h3 class="servicios-card__title">{{ $service->title }}</h3>
                        @if($service->subtitle)
                        <p class="servicios-card__subtitle">{{ $service->subtitle }}</p>
                        @endif
                        <a href="{{ route('servicios.show', $service->slug) }}" class="servicios-card__btn">
                            <span class="servicios-card__btn-text">Ver todas</span>
                            <svg class="servicios-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5.8335 5.8335H14.1668M14.1668 5.8335V14.1668M14.1668 5.8335L5.8335 14.1668" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif

        @if($equipments->isNotEmpty())
        <section class="hc-search-page__group">
            <div class="hc-search-page__group-head">
                <h2 class="hc-search-page__group-title">Equipos</h2>
                <span class="hc-search-page__group-count">{{ $equipments->count() }}</span>
            </div>
            <div class="equipos-page__grid">
                @foreach($equipments as $equipment)
                <article class="equipos-card">
                    <div class="equipos-card__img-wrap">
                        <img src="{{ $equipment->main_image_url }}" alt="{{ $equipment->title }}" loading="lazy">
                    </div>
                    <div class="equipos-card__body">
                        <svg class="equipos-card__divider" xmlns="http://www.w3.org/2000/svg" width="307" height="1" viewBox="0 0 307 1" fill="none" aria-hidden="true">
                            <path d="M0 0.5L307 0.500027" stroke="#C5C5C5"/>
                        </svg>
                        <h3 class="equipos-card__title">{{ $equipment->title }}</h3>
                        <a href="{{ route('equipos.show', $equipment->slug ?: $equipment->id) }}" class="equipos-card__btn">
                            <span class="equipos-card__btn-text">Ver más</span>
                            <svg class="equipos-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5.83325 5.8335H14.1666M14.1666 5.8335V14.1668M14.1666 5.8335L5.83325 14.1668" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif

        @if($projects->isNotEmpty())
        <section class="hc-search-page__group">
            <div class="hc-search-page__group-head">
                <h2 class="hc-search-page__group-title">Trabajos realizados</h2>
                <span class="hc-search-page__group-count">{{ $projects->count() }}</span>
            </div>
            <div class="trabajos-page__grid">
                @foreach($projects as $project)
                <div class="trabajos-card" data-search-project="{{ $project->id }}" role="button" tabindex="0" aria-label="Ver galería: {{ $project->title }}">
                    <div class="trabajos-card__img">
                        <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }}" loading="lazy">
                    </div>
                    <div class="trabajos-card__body">
                        <span class="trabajos-card__title">{{ $project->title }}</span>
                        <button class="trabajos-card__btn" aria-hidden="true" tabindex="-1">
                            <svg class="trabajos-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5.83325 5.83337H14.1666M14.1666 5.83337V14.1667M14.1666 5.83337L5.83325 14.1667" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</section>

@if($projects->isNotEmpty())
<div class="trabajos-modal" id="searchProjectModal" role="dialog" aria-modal="true" aria-label="Galería de proyecto" hidden>
    <div class="trabajos-modal__backdrop" id="searchModalBackdrop"></div>
    <div class="trabajos-modal__box">
        <button class="trabajos-modal__close" id="searchModalClose" aria-label="Cerrar galería">
            <svg xmlns="http://www.w3.org/2000/svg" width="37" height="37" viewBox="0 0 37 37" fill="none" aria-hidden="true">
                <path d="M18.4997 20.6585L22.9705 25.1293C23.2531 25.412 23.6129 25.5533 24.0497 25.5533C24.4865 25.5533 24.8462 25.412 25.1288 25.1293C25.4115 24.8467 25.5528 24.487 25.5528 24.0502C25.5528 23.6134 25.4115 23.2536 25.1288 22.971L20.658 18.5002L25.1288 14.0293C25.4115 13.7467 25.5528 13.387 25.5528 12.9502C25.5528 12.5134 25.4115 12.1536 25.1288 11.871C24.8462 11.5884 24.4865 11.447 24.0497 11.447C23.6129 11.447 23.2531 11.5884 22.9705 11.871L18.4997 16.3418L14.0288 11.871C13.7462 11.5884 13.3865 11.447 12.9497 11.447C12.5129 11.447 12.1531 11.5884 11.8705 11.871C11.5879 12.1536 11.4466 12.5134 11.4466 12.9502C11.4466 13.387 11.5879 13.7467 11.8705 14.0293L16.3413 18.5002L11.8705 22.971C11.5879 23.2536 11.4466 23.6134 11.4466 24.0502C11.4466 24.487 11.5879 24.8467 11.8705 25.1293C12.1531 25.412 12.5129 25.5533 12.9497 25.5533C13.3865 25.5533 13.7462 25.412 14.0288 25.1293L18.4997 20.6585ZM18.4997 33.9168C16.367 33.9168 14.3629 33.5119 12.4872 32.702C10.6115 31.8921 8.97988 30.7939 7.59238 29.4075C6.20489 28.021 5.1067 26.3894 4.29784 24.5127C3.48898 22.6359 3.08404 20.6318 3.08301 18.5002C3.08198 16.3686 3.48693 14.3644 4.29784 12.4877C5.10876 10.6109 6.20694 8.97934 7.59238 7.59287C8.97783 6.2064 10.6094 5.10822 12.4872 4.29833C14.3649 3.48844 16.3691 3.0835 18.4997 3.0835C20.6303 3.0835 22.6344 3.48844 24.5122 4.29833C26.3899 5.10822 28.0215 6.2064 29.407 7.59287C30.7924 8.97934 31.8911 10.6109 32.7031 12.4877C33.515 14.3644 33.9194 16.3686 33.9163 18.5002C33.9133 20.6318 33.5083 22.6359 32.7015 24.5127C31.8947 26.3894 30.7965 28.021 29.407 29.4075C28.0174 30.7939 26.3858 31.8926 24.5122 32.7035C22.6385 33.5145 20.6344 33.9189 18.4997 33.9168ZM18.4997 30.8335C21.9427 30.8335 24.859 29.6387 27.2486 27.2491C29.6382 24.8595 30.833 21.9432 30.833 18.5002C30.833 15.0571 29.6382 12.1408 27.2486 9.7512C24.859 7.36162 21.9427 6.16683 18.4997 6.16683C15.0566 6.16683 12.1403 7.36162 9.75072 9.7512C7.36113 12.1408 6.16634 15.0571 6.16634 18.5002C6.16634 21.9432 7.36113 24.8595 9.75072 27.2491C12.1403 29.6387 15.0566 30.8335 18.4997 30.8335Z" fill="#AEAEB2"/>
            </svg>
        </button>
        <div class="trabajos-modal__slider">
            <div class="trabajos-modal__slides" id="searchModalSlides"></div>
        </div>
        <div class="trabajos-modal__dots" id="searchModalDots"></div>
    </div>
</div>

<script>
var searchProjectsData = {};
@foreach($projects as $project)
searchProjectsData[{{ $project->id }}] = {
    title: "{{ addslashes($project->title) }}",
    images: [
        @if($project->cover_image)
        { url: "{{ $project->cover_image_url }}", alt: "{{ addslashes($project->title) }}" },
        @endif
        @foreach($project->images as $img)
        { url: "{{ $img->image_url }}", alt: "{{ addslashes($project->title) }}" },
        @endforeach
    ]
};
@endforeach
</script>

@push('scripts')
<script>
(() => {
  const modal = document.getElementById('searchProjectModal');
  const backdrop = document.getElementById('searchModalBackdrop');
  const slidesEl = document.getElementById('searchModalSlides');
  const dotsEl = document.getElementById('searchModalDots');
  const btnClose = document.getElementById('searchModalClose');
  if (!modal || !slidesEl || !dotsEl || !btnClose) return;

  let current = 0;
  let images = [];

  const render = () => {
    slidesEl.innerHTML = '';
    dotsEl.innerHTML = '';

    const slide = document.createElement('div');
    slide.className = 'trabajos-modal__slide is-active';

    images.slice(0, 2).forEach((img) => {
      const media = document.createElement('div');
      media.className = 'trabajos-modal__media';
      const image = document.createElement('img');
      image.src = img.url;
      image.alt = img.alt;
      image.loading = 'lazy';
      media.appendChild(image);
      slide.appendChild(media);
    });

    slidesEl.appendChild(slide);

    images.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'trabajos-modal__dot' + (index === current ? ' is-active' : '');
      dot.setAttribute('aria-label', 'Imagen ' + (index + 1));
      dot.addEventListener('click', () => {
        current = index;
        dotsEl.querySelectorAll('.trabajos-modal__dot').forEach((item, itemIndex) => {
          item.classList.toggle('is-active', itemIndex === current);
        });
      });
      dotsEl.appendChild(dot);
    });

    dotsEl.hidden = images.length <= 1;
  };

  const openModal = (projectId) => {
    const data = searchProjectsData[projectId];
    if (!data || !data.images.length) return;
    images = data.images;
    current = 0;
    render();
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    modal.hidden = true;
    document.body.style.overflow = '';
  };

  document.querySelectorAll('.trabajos-card[data-search-project]').forEach((card) => {
    card.addEventListener('click', () => openModal(parseInt(card.dataset.searchProject, 10)));
    card.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openModal(parseInt(card.dataset.searchProject, 10));
      }
    });
  });

  btnClose.addEventListener('click', closeModal);
  backdrop.addEventListener('click', closeModal);
  document.addEventListener('keydown', (event) => {
    if (modal.hidden) return;
    if (event.key === 'Escape') closeModal();
  });
})();
</script>
@endpush
@endif
@endsection
