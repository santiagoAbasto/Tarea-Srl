<section class="hc-section hc-projects">
    <div class="hc-container">

        @if($section?->title)
        <h2 class="hc-projects__title">{{ $section->title }}</h2>
        @endif

        <div class="hc-projects__grid">
            @forelse($projects as $project)
            <div class="hc-project-card" data-project="{{ $project->id }}" role="button" tabindex="0"
                 aria-label="Ver galería: {{ $project->title }}">
                <div class="hc-project-card__img">
                    <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }}" loading="lazy">
                </div>
                <div class="hc-project-card__overlay">
                    <span class="hc-project-card__title">{{ $project->title }}</span>
                    <button class="hc-project-card__btn" aria-hidden="true" tabindex="-1">
                        <svg class="hc-project-card__btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5.83325 5.83337H14.1666M14.1666 5.83337V14.1667M14.1666 5.83337L5.83325 14.1667" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            @empty
            <p class="hc-empty">No hay proyectos disponibles.</p>
            @endforelse
        </div>

        @if($projects->count())
        <div class="hc-projects__footer">
            <a href="{{ route('proyectos.index') }}" class="hc-projects__all-btn">
                <span class="hc-projects__all-btn-text">Ver todos</span>
                <svg class="hc-projects__all-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5.83325 5.83325H14.1666M14.1666 5.83325V14.1666M14.1666 5.83325L5.83325 14.1666" stroke="#D50101" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        @endif

    </div>
</section>

{{-- MODAL --}}
<div class="trabajos-modal" id="hcProjectModal" role="dialog" aria-modal="true"
     aria-label="Galería de proyecto" hidden>
    <div class="trabajos-modal__backdrop" id="hcModalBackdrop"></div>
    <div class="trabajos-modal__box">
        <button class="trabajos-modal__close" id="hcModalClose" aria-label="Cerrar galería">
            <svg xmlns="http://www.w3.org/2000/svg" width="37" height="37" viewBox="0 0 37 37" fill="none" aria-hidden="true">
                <path d="M18.4997 20.6585L22.9705 25.1293C23.2531 25.412 23.6129 25.5533 24.0497 25.5533C24.4865 25.5533 24.8462 25.412 25.1288 25.1293C25.4115 24.8467 25.5528 24.487 25.5528 24.0502C25.5528 23.6134 25.4115 23.2536 25.1288 22.971L20.658 18.5002L25.1288 14.0293C25.4115 13.7467 25.5528 13.387 25.5528 12.9502C25.5528 12.5134 25.4115 12.1536 25.1288 11.871C24.8462 11.5884 24.4865 11.447 24.0497 11.447C23.6129 11.447 23.2531 11.5884 22.9705 11.871L18.4997 16.3418L14.0288 11.871C13.7462 11.5884 13.3865 11.447 12.9497 11.447C12.5129 11.447 12.1531 11.5884 11.8705 11.871C11.5879 12.1536 11.4466 12.5134 11.4466 12.9502C11.4466 13.387 11.5879 13.7467 11.8705 14.0293L16.3413 18.5002L11.8705 22.971C11.5879 23.2536 11.4466 23.6134 11.4466 24.0502C11.4466 24.487 11.5879 24.8467 11.8705 25.1293C12.1531 25.412 12.5129 25.5533 12.9497 25.5533C13.3865 25.5533 13.7462 25.412 14.0288 25.1293L18.4997 20.6585ZM18.4997 33.9168C16.367 33.9168 14.3629 33.5119 12.4872 32.702C10.6115 31.8921 8.97988 30.7939 7.59238 29.4075C6.20489 28.021 5.1067 26.3894 4.29784 24.5127C3.48898 22.6359 3.08404 20.6318 3.08301 18.5002C3.08198 16.3686 3.48693 14.3644 4.29784 12.4877C5.10876 10.6109 6.20694 8.97934 7.59238 7.59287C8.97783 6.2064 10.6094 5.10822 12.4872 4.29833C14.3649 3.48844 16.3691 3.0835 18.4997 3.0835C20.6303 3.0835 22.6344 3.48844 24.5122 4.29833C26.3899 5.10822 28.0215 6.2064 29.407 7.59287C30.7924 8.97934 31.8911 10.6109 32.7031 12.4877C33.515 14.3644 33.9194 16.3686 33.9163 18.5002C33.9133 20.6318 33.5083 22.6359 32.7015 24.5127C31.8947 26.3894 30.7965 28.021 29.407 29.4075C28.0174 30.7939 26.3858 31.8926 24.5122 32.7035C22.6385 33.5145 20.6344 33.9189 18.4997 33.9168ZM18.4997 30.8335C21.9427 30.8335 24.859 29.6387 27.2486 27.2491C29.6382 24.8595 30.833 21.9432 30.833 18.5002C30.833 15.0571 29.6382 12.1408 27.2486 9.7512C24.859 7.36162 21.9427 6.16683 18.4997 6.16683C15.0566 6.16683 12.1403 7.36162 9.75072 9.7512C7.36113 12.1408 6.16634 15.0571 6.16634 18.5002C6.16634 21.9432 7.36113 24.8595 9.75072 27.2491C12.1403 29.6387 15.0566 30.8335 18.4997 30.8335Z" fill="#AEAEB2"/>
            </svg>
        </button>
        <div class="trabajos-modal__slider">
            <div class="trabajos-modal__slides" id="hcModalSlides"></div>
        </div>
        <div class="trabajos-modal__dots" id="hcModalDots"></div>
    </div>
</div>

{{-- PROJECT DATA --}}
<script>
var hcProjectsData = {};
@foreach($projects as $project)
hcProjectsData[{{ $project->id }}] = {
    title: "{{ addslashes($project->title) }}",
    images: [
        @if($project->cover_image)
        { url: "{{ $project->cover_image_url }}", alt: "{{ addslashes($project->title) }}" },
        @endif
        @foreach($project->images->where('is_active', true)->sortBy('order') as $img)
        { url: "{{ $img->image_url }}", alt: "{{ addslashes($project->title) }}" },
        @endforeach
        @if(!$project->cover_image && $project->images->where('is_active', true)->isEmpty())
        { url: "{{ $project->cover_image_url }}", alt: "{{ addslashes($project->title) }}" }
        @endif
    ]
};
@endforeach
</script>

@push('scripts')
<script>
(function(){
    var modal    = document.getElementById('hcProjectModal');
    var backdrop = document.getElementById('hcModalBackdrop');
    var slidesEl = document.getElementById('hcModalSlides');
    var dotsEl   = document.getElementById('hcModalDots');
    var btnClose = document.getElementById('hcModalClose');

    var current = 0, imgs = [], pages = [], currentProjectId = null;
    var touchStartX = 0, touchStartY = 0;
    var lockedScrollY = 0;

    function normalizeImages(images){
        var seen = new Set();

        return images.filter(function(image){
            if(!image || !image.url || seen.has(image.url)) return false;
            seen.add(image.url);
            return true;
        });
    }

    function buildPages(images){
        var normalized = normalizeImages(images);
        var itemsPerPage = window.innerWidth <= 768 ? 1 : 2;
        var grouped = [];

        for (var i = 0; i < normalized.length; i += itemsPerPage) {
            grouped.push(normalized.slice(i, i + itemsPerPage));
        }

        return grouped;
    }

    function openModal(projectId){
        var data = hcProjectsData[projectId];
        if(!data || !data.images.length) return;
        currentProjectId = projectId;
        imgs = data.images;
        pages = buildPages(imgs);
        current = 0;
        render();
        modal.hidden = false;
        lockedScrollY = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + lockedScrollY + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';
        btnClose.focus();
    }

    function closeModal(){
        modal.hidden = true;
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        window.scrollTo(0, lockedScrollY);
    }

    function render(){
        slidesEl.innerHTML = '';
        dotsEl.innerHTML   = '';
        pages.forEach(function(page, i){
            var div = document.createElement('div');
            div.className = 'trabajos-modal__slide' + (i === current ? ' is-active' : '');
            page.forEach(function(img){
                var wrap = document.createElement('div');
                wrap.className = 'trabajos-modal__media';
                var el = document.createElement('img');
                el.src = img.url;
                el.alt = img.alt;
                el.loading = 'lazy';
                wrap.appendChild(el);
                div.appendChild(wrap);
            });
            slidesEl.appendChild(div);

            var dot = document.createElement('button');
            dot.className = 'trabajos-modal__dot' + (i === current ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Página ' + (i + 1));
            dot.dataset.index = i;
            dot.addEventListener('click', function(){ goTo(parseInt(this.dataset.index, 10)); });
            dotsEl.appendChild(dot);
        });

        dotsEl.hidden = pages.length <= 1;
    }

    function goTo(i){
        current = Math.max(0, Math.min(i, pages.length - 1));
        render();
    }

    // Open on card click / enter
    document.querySelectorAll('.hc-project-card').forEach(function(card){
        card.addEventListener('click', function(){ openModal(parseInt(this.dataset.project, 10)); });
        card.addEventListener('keydown', function(e){
            if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); openModal(parseInt(this.dataset.project, 10)); }
        });
    });

    btnClose.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    slidesEl.addEventListener('touchstart', function(e){
        var touch = e.changedTouches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
    }, { passive: true });

    slidesEl.addEventListener('touchmove', function(e){
        if(modal.hidden || pages.length <= 1) return;

        var touch = e.changedTouches[0];
        var deltaX = touch.clientX - touchStartX;
        var deltaY = touch.clientY - touchStartY;

        if(Math.abs(deltaX) > Math.abs(deltaY)) {
            e.preventDefault();
        }
    }, { passive: false });

    slidesEl.addEventListener('touchend', function(e){
        if(modal.hidden || pages.length <= 1) return;

        var touch = e.changedTouches[0];
        var deltaX = touch.clientX - touchStartX;
        var deltaY = touch.clientY - touchStartY;

        if(Math.abs(deltaX) < 30 || Math.abs(deltaX) <= Math.abs(deltaY)) return;

        if(deltaX < 0) goTo(current + 1);
        else goTo(current - 1);
    }, { passive: true });

    document.addEventListener('keydown', function(e){
        if(modal.hidden) return;
        if(e.key === 'Escape')     closeModal();
        if(e.key === 'ArrowRight') goTo(current + 1);
        if(e.key === 'ArrowLeft')  goTo(current - 1);
    });

    window.addEventListener('resize', function(){
        if(modal.hidden || currentProjectId === null || !hcProjectsData[currentProjectId]) return;
        pages = buildPages(hcProjectsData[currentProjectId].images);
        current = Math.min(current, pages.length - 1);
        render();
    });
}());
</script>
@endpush
