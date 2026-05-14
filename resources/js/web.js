/* =============================================
   WEB TOAST — iOS-style notification
   Llamar con: showWebToast(mensaje, 'success' | 'error')
============================================= */

(function () {

    var SVG_OK = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">'
        + '<circle cx="12" cy="12" r="11" fill="#136CBA" opacity="0.12"/>'
        + '<path d="M7 12.5l3.5 3.5 6.5-7" stroke="#136CBA" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>'
        + '</svg>';

    var SVG_ERR = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">'
        + '<circle cx="12" cy="12" r="11" fill="#ef4444" opacity="0.12"/>'
        + '<path d="M15 9L9 15M9 9l6 6" stroke="#ef4444" stroke-width="2.1" stroke-linecap="round"/>'
        + '</svg>';

    var SVG_X = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none">'
        + '<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>'
        + '</svg>';

    function showWebToast(message, type) {
        type = type || 'success';

        /* Elimina toasts anteriores */
        document.querySelectorAll('.web-toast').forEach(function (t) { t.remove(); });

        var toast = document.createElement('div');
        toast.className = 'web-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');

        var icon = document.createElement('span');
        icon.className = 'web-toast__icon';
        icon.innerHTML = type === 'success' ? SVG_OK : SVG_ERR;

        var text = document.createElement('span');
        text.className = 'web-toast__msg';
        text.textContent = message;

        var close = document.createElement('button');
        close.className = 'web-toast__close';
        close.setAttribute('aria-label', 'Cerrar');
        close.setAttribute('type', 'button');
        close.innerHTML = SVG_X;

        toast.appendChild(icon);
        toast.appendChild(text);
        toast.appendChild(close);

        document.body.appendChild(toast);

        function dismiss() {
            toast.classList.add('web-toast--hide');
            setTimeout(function () { if (toast.parentNode) toast.remove(); }, 340);
        }

        close.addEventListener('click', dismiss);

        /* Auto-dismiss: 4s éxito / 6s error */
        var timer = setTimeout(dismiss, type === 'error' ? 6000 : 4000);

        /* Pausa al pasar el mouse */
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 2000); });
    }

    window.showWebToast = showWebToast;

}());


/* =============================================
   NEWSLETTER — AJAX (sin recarga de página)
   Intercepta el submit, envía con fetch,
   muestra toast iOS inline y se queda en el footer.
============================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("newsletterForm");
    const msg  = document.getElementById("newsletterMsg");   // aria-live oculto

    if (form && msg) {

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const btn   = form.querySelector("button[type='submit']");
            const input = form.querySelector("input[type='email']");
            const csrf  = form.querySelector("input[name='_token']").value;

            /* Estado: enviando */
            btn.disabled      = true;
            btn.style.opacity = "0.6";
            msg.textContent   = "";

            try {
                const res = await fetch(form.action, {
                    method:  "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                        "X-CSRF-TOKEN": csrf,
                        "Accept":       "application/json",
                    },
                    body: new URLSearchParams({ email: input.value, _token: csrf }),
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    input.value     = "";
                    msg.textContent = "¡Suscripción realizada!";
                    showWebToast("¡Te suscribiste correctamente!", "success");
                } else {
                    const errores  = data?.errors?.email;
                    const errorMsg = errores
                        ? errores[0]
                        : (data?.message || "Ocurrió un error. Intentá de nuevo.");
                    msg.textContent = errorMsg;
                    showWebToast(errorMsg, "error");
                }

            } catch {
                const errMsg = "Error de red. Intentá de nuevo.";
                msg.textContent = errMsg;
                showWebToast(errMsg, "error");
            }

            btn.disabled      = false;
            btn.style.opacity = "";
        });
    }

});


document.addEventListener("DOMContentLoaded", () => {

    /* =============================================
       MOBILE NAVBAR — JS fix
       Garantiza position:fixed en mobile/tablet
       independientemente de quirks de iOS Safari.
       overflow-x: clip en html (reset.css) resuelve
       el horizontal overflow del panel de nav.
    ============================================= */

    const nav = document.getElementById("mainNav");

    if (nav) {
        const isCatalogHeader = nav.classList.contains("site-header--catalog");

        function applyMobileFixed() {
            if (window.innerWidth <= 1024) {
                nav.style.position = "fixed";
                nav.style.top      = "0";
                nav.style.left     = "0";
                nav.style.right    = "0";
                nav.style.width    = "100%";
                nav.style.zIndex   = "100";
                document.body.style.paddingTop = isCatalogHeader ? nav.offsetHeight + "px" : "";
            } else {
                /* Desktop: quita los estilos inline y deja actuar al CSS */
                nav.style.position = "";
                nav.style.top      = "";
                nav.style.left     = "";
                nav.style.right    = "";
                nav.style.width    = "";
                nav.style.zIndex   = "";
                document.body.style.paddingTop = "";
            }
        }

        applyMobileFixed();
        window.addEventListener("resize", applyMobileFixed, { passive: true });
    }


    /* =============================================
       HERO SLIDER
       - Auto-avance configurable por slide
       - Dots sincronizan con el slide activo
       - Click en dot navega al slide correspondiente
       - Funciona con cualquier cantidad de slides
    ============================================= */

    const heroSection = document.querySelector(".hero");

    if (heroSection) {

        const slides   = heroSection.querySelectorAll(".hero-slide");
        const dots     = heroSection.querySelectorAll(".hero-dot");
        const contents = heroSection.querySelectorAll(".hero-content");
        const heroMobileQuery = window.matchMedia("(max-width: 768px)");
        let currentIndex = Array.from(slides).findIndex((slide) => slide.classList.contains("active"));

        if (currentIndex < 0) {
            currentIndex = 0;
        }

        function heroPreferredSrc(node) {
            if (!node) {
                return "";
            }

            const desktopSrc = node.dataset.desktopSrc || "";
            const mobileSrc = node.dataset.mobileSrc || "";

            return heroMobileQuery.matches ? (mobileSrc || desktopSrc) : (desktopSrc || mobileSrc);
        }

        function heroPreferredType(node) {
            if (!node) {
                return "";
            }

            const desktopType = node.dataset.desktopType || "";
            const mobileType = node.dataset.mobileType || "";

            return heroMobileQuery.matches ? (mobileType || desktopType) : (desktopType || mobileType);
        }

        function syncHeroSlideMedia(slide, isActive) {
            if (!slide) {
                return;
            }

            slide.querySelectorAll("iframe[data-desktop-src], iframe[data-mobile-src]").forEach((iframe) => {
                const nextSrc = isActive ? heroPreferredSrc(iframe) : "";

                if (iframe.getAttribute("src") !== nextSrc) {
                    iframe.setAttribute("src", nextSrc);
                }
            });

            slide.querySelectorAll("video[data-desktop-src], video[data-mobile-src]").forEach((video) => {
                const source = video.querySelector("source");

                if (!source) {
                    return;
                }

                const nextSrc = isActive ? heroPreferredSrc(video) : "";
                const nextType = heroPreferredType(video);
                const currentSrc = source.getAttribute("src") || "";

                if (currentSrc !== nextSrc) {
                    source.setAttribute("src", nextSrc);
                    if (nextType) {
                        source.setAttribute("type", nextType);
                    }
                    video.load();
                }

                if (isActive && nextSrc) {
                    video.muted = true;
                    video.defaultMuted = true;
                    video.playsInline = true;
                    const playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === "function") {
                        playPromise.catch(() => {});
                    }
                } else {
                    video.pause();
                }
            });
        }

        function syncHeroMediaForViewport() {
            slides.forEach((slide, index) => syncHeroSlideMedia(slide, index === currentIndex));
        }

        syncHeroMediaForViewport();

        if (typeof heroMobileQuery.addEventListener === "function") {
            heroMobileQuery.addEventListener("change", syncHeroMediaForViewport);
        } else if (typeof heroMobileQuery.addListener === "function") {
            heroMobileQuery.addListener(syncHeroMediaForViewport);
        }

        if (slides.length > 1) {

            let autoplay;

            function getHeroDelay() {
                const seconds = Number(slides[currentIndex]?.dataset?.autoplaySeconds || 6);

                if (!Number.isFinite(seconds) || seconds <= 0) {
                    return 6000;
                }

                return seconds * 1000;
            }

            function heroGoTo(index) {
                slides[currentIndex].classList.remove("active");
                if (dots[currentIndex])     dots[currentIndex].classList.remove("active");
                if (contents[currentIndex]) contents[currentIndex].classList.remove("active");

                currentIndex = (index + slides.length) % slides.length;

                slides[currentIndex].classList.add("active");
                if (dots[currentIndex])     dots[currentIndex].classList.add("active");
                if (contents[currentIndex]) contents[currentIndex].classList.add("active");
                syncHeroMediaForViewport();
            }

            function startAutoplay() {
                clearInterval(autoplay);
                autoplay = setInterval(() => heroGoTo(currentIndex + 1), getHeroDelay());
            }

            function resetAutoplay() {
                startAutoplay();
            }

            dots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    if (i !== currentIndex) { heroGoTo(i); resetAutoplay(); }
                });
            });

            startAutoplay();
        }
    }

    /* =============================================
       PRODUCTS BANNER SLIDER
       - Mismo comportamiento que el hero slider
       - Soporte imagen y video (data-src en iframe)
    ============================================= */

    const productsBanner = document.querySelector(".products-banner");

    if (productsBanner) {

        const pbSlides = productsBanner.querySelectorAll(".products-banner__slide");
        const pbDots   = productsBanner.querySelectorAll(".products-banner__dot");

        if (pbSlides.length > 1) {

            let pbCurrent = 0;
            let pbAutoplay;

            function pbGoTo(index) {
                pbSlides[pbCurrent].classList.remove("active");
                if (pbDots[pbCurrent]) pbDots[pbCurrent].classList.remove("active");

                const oldIframe = pbSlides[pbCurrent].querySelector("iframe");
                if (oldIframe) oldIframe.src = "";

                pbCurrent = (index + pbSlides.length) % pbSlides.length;

                pbSlides[pbCurrent].classList.add("active");
                if (pbDots[pbCurrent]) pbDots[pbCurrent].classList.add("active");

                const newIframe = pbSlides[pbCurrent].querySelector("iframe");
                if (newIframe && newIframe.dataset.src) {
                    newIframe.src = newIframe.dataset.src;
                }
            }

            function pbStartAutoplay() {
                pbAutoplay = setInterval(() => pbGoTo(pbCurrent + 1), 6000);
            }

            function pbResetAutoplay() {
                clearInterval(pbAutoplay);
                pbStartAutoplay();
            }

            pbDots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    if (i !== pbCurrent) { pbGoTo(i); pbResetAutoplay(); }
                });
            });

            pbStartAutoplay();
        }
    }


    /* =============================================
       HAMBURGER MENU
    ============================================= */

    const hamburger = document.getElementById("hamburgerBtn");
    const navLinks  = document.getElementById("navLinks");
    const navClose  = document.getElementById("navClose");
    const overlay   = document.getElementById("navOverlay");

    function openMenu() {
        if (!navLinks || !overlay || !hamburger) return;
        navLinks.classList.add("open");
        overlay.classList.add("active");
        hamburger.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
    }

    function closeMenu() {
        if (!navLinks || !overlay || !hamburger) return;
        navLinks.classList.remove("open");
        overlay.classList.remove("active");
        hamburger.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
    }

    if (hamburger) {
        hamburger.addEventListener("click", () => {
            navLinks.classList.contains("open") ? closeMenu() : openMenu();
        });

        navClose.addEventListener("click", closeMenu);
        overlay.addEventListener("click", closeMenu);

        navLinks.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", closeMenu);
        });
    }

    /* =============================================
       HEADER SCROLL — fondo sólido al desplazarse
       Solo activo cuando el header es fixed (≤1024px)
       para no afectar el comportamiento desktop.
    ============================================= */

    if (nav) {
        const updateHeaderBg = () => {
            if (window.innerWidth <= 1024 && window.scrollY > 20) {
                nav.classList.add("site-header--scrolled");
            } else {
                nav.classList.remove("site-header--scrolled");
            }
        };
        window.addEventListener("scroll", updateHeaderBg, { passive: true });
        window.addEventListener("resize", updateHeaderBg, { passive: true });
        updateHeaderBg();
    }

    /* =============================================
       HEADER SEARCH
    ============================================= */

    const searchModal = document.getElementById("headerSearchModal");
    const searchBackdrop = document.getElementById("headerSearchBackdrop");
    const searchClose = document.getElementById("headerSearchClose");
    const searchInput = document.getElementById("headerSearchInput");
    const searchOpenButtons = document.querySelectorAll("#headerSearchOpen, #headerSearchOpenMobile");

    function openSearch() {
        if (!searchModal) return;
        searchModal.hidden = false;
        document.body.style.overflow = "hidden";
        window.setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 20);
    }

    function closeSearch() {
        if (!searchModal) return;
        searchModal.hidden = true;
        document.body.style.overflow = "";
    }

    searchOpenButtons.forEach((button) => {
        button.addEventListener("click", () => {
            closeMenu();
            openSearch();
        });
    });

    if (searchClose) searchClose.addEventListener("click", closeSearch);
    if (searchBackdrop) searchBackdrop.addEventListener("click", closeSearch);

    document.addEventListener("keydown", (event) => {
        if (searchModal && !searchModal.hidden && event.key === "Escape") {
            closeSearch();
        }
    });

    /* =============================================
       PRESUPUESTO SELECT ARROW
    ============================================= */

    document.querySelectorAll(".presupuesto-select__arrow").forEach((arrow) => {
        arrow.addEventListener("click", () => {
            const select = arrow.parentElement ? arrow.parentElement.querySelector("select") : null;
            if (!select) return;
            select.focus();
            if (typeof select.showPicker === "function") {
                select.showPicker();
            } else {
                select.click();
            }
        });
    });

});


/* =============================================
   PAGE TRANSITIONS — prefetch + exit fade
   - Hover: prefetch inmediato del destino
   - Click: fade-out 80ms antes de navegar
     (solo en browsers sin View Transitions API,
      donde la API lo maneja nativa y automáticamente)
============================================= */

(function () {
    const isInternal = (href) => {
        if (!href) return false;
        if (/^(#|javascript:|mailto:|tel:)/.test(href)) return false;
        try {
            return new URL(href, location.href).origin === location.origin;
        } catch { return false; }
    };

    /* ── Prefetch on hover ── */
    const prefetched = new Set();
    document.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!isInternal(href) || prefetched.has(href)) return;
        prefetched.add(href);
        const link = document.createElement('link');
        link.rel  = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
    }, { passive: true });

    /* ── Exit animation on click (fallback: no View Transitions) ── */
    if (!document.startViewTransition) {
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || a.target === '_blank') return;
            const href = a.getAttribute('href');
            if (!isInternal(href)) return;
            e.preventDefault();
            document.body.style.transition = 'opacity 0.08s ease, transform 0.08s ease';
            document.body.style.opacity   = '0';
            document.body.style.transform = 'translateY(-4px)';
            setTimeout(() => { location.href = href; }, 80);
        });
    }
}());


/* =============================================
   NOVEDADES SLIDER
   - Muestra grupos de 3 posts
   - Dots generados dinámicamente por blade
   - Click en dot navega al grupo correspondiente
   - Solo activo si hay más de 1 grupo
============================================= */

document.addEventListener("DOMContentLoaded", () => {

    const novedades = document.querySelector(".novedades");
    if (!novedades) return;

    const slides = novedades.querySelectorAll(".novedades__slide");
    const dots   = novedades.querySelectorAll(".novedades__dot");

    if (slides.length <= 1) return;

    let current = 0;

    function novedadesGoTo(index) {
        slides[current].classList.remove("active");
        if (dots[current]) dots[current].classList.remove("active");

        current = (index + slides.length) % slides.length;

        slides[current].classList.add("active");
        if (dots[current]) dots[current].classList.add("active");
    }

    dots.forEach((dot, i) => {
        dot.addEventListener("click", () => {
            if (i !== current) novedadesGoTo(i);
        });
    });

});


/* =============================================
   PRODUCT SHOW GALLERY
   - 5 miniaturas visibles por fila
   - Scroll interno desde la 6ta
   - Auto-play cada 3s
   - Click en miniatura pausa
   - Click fuera de miniaturas reanuda
============================================= */

document.addEventListener("DOMContentLoaded", () => {

    const galleries = document.querySelectorAll("[data-product-gallery]");

    galleries.forEach((gallery) => {
        const main = gallery.querySelector("[data-product-gallery-main]");
        const thumbsRail = gallery.querySelector("[data-product-gallery-thumbs]");
        const thumbs = Array.from(gallery.querySelectorAll("[data-gallery-thumb]"));

        if (!main || !thumbsRail || thumbs.length === 0) {
            return;
        }

        const primaryLayer = gallery.querySelector('[data-gallery-image-layer="primary"]');
        const secondaryLayer = gallery.querySelector('[data-gallery-image-layer="secondary"]');

        if (!primaryLayer || !secondaryLayer) {
            return;
        }

        const autoplayDelay = Number.parseInt(gallery.dataset.autoplayDelay || "3000", 10);
        let activeIndex = Math.max(thumbs.findIndex((thumb) => thumb.classList.contains("is-active")), 0);
        let currentLayer = primaryLayer;
        let nextLayer = secondaryLayer;
        let autoplayTimer = null;
        let manualSelection = false;
        let transitionCleanupTimer = null;
        const preloadedSources = new Set(
            thumbs
                .map((thumb) => thumb.dataset.mediaSrc || "")
                .filter(Boolean)
                .slice(0, 1)
        );

        function syncThumbState(index) {
            thumbs.forEach((thumb, thumbIndex) => {
                const isActive = thumbIndex === index;
                thumb.classList.toggle("is-active", isActive);
                thumb.setAttribute("aria-current", isActive ? "true" : "false");
            });
        }

        function scrollThumbIntoView(index) {
            const thumb = thumbs[index];

            if (!thumb) {
                return;
            }

            thumb.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
                inline: "nearest",
            });
        }

        function imagePayloadFor(index) {
            const thumb = thumbs[index];

            if (!thumb) {
                return null;
            }

            return {
                src: thumb.dataset.mediaSrc || "",
                alt: thumb.dataset.mediaAlt || "",
            };
        }

        function cleanupLeavingLayer() {
            if (transitionCleanupTimer) {
                window.clearTimeout(transitionCleanupTimer);
            }

            transitionCleanupTimer = window.setTimeout(() => {
                nextLayer.classList.remove("is-leaving");
                nextLayer.setAttribute("aria-hidden", "true");
            }, 720);
        }

        function preloadImage(payload) {
            if (!payload || !payload.src || preloadedSources.has(payload.src)) {
                return Promise.resolve();
            }

            return new Promise((resolve) => {
                const image = new Image();

                image.onload = () => {
                    preloadedSources.add(payload.src);
                    resolve();
                };

                image.onerror = () => resolve();
                image.src = payload.src;

                if (typeof image.decode === "function") {
                    image.decode()
                        .then(() => {
                            preloadedSources.add(payload.src);
                            resolve();
                        })
                        .catch(() => {});
                }
            });
        }

        async function renderImage(index, options) {
            const settings = options || {};
            const animate = settings.animate !== false;
            const payload = imagePayloadFor(index);

            if (!payload || !payload.src) {
                return;
            }

            syncThumbState(index);
            scrollThumbIntoView(index);

            if (activeIndex === index && currentLayer.getAttribute("src") === payload.src) {
                return;
            }

            await preloadImage(payload);

            const incomingLayer = nextLayer;
            const outgoingLayer = currentLayer;

            incomingLayer.setAttribute("src", payload.src);
            incomingLayer.setAttribute("alt", payload.alt);
            incomingLayer.removeAttribute("aria-hidden");
            incomingLayer.classList.remove("is-leaving");

            if (!animate) {
                outgoingLayer.classList.remove("is-active", "is-leaving");
                outgoingLayer.setAttribute("aria-hidden", "true");
                incomingLayer.classList.add("is-active");
                currentLayer = incomingLayer;
                nextLayer = outgoingLayer;
                activeIndex = index;
                return;
            }

            // Fuerza el reflow para que la transición siempre dispare.
            void incomingLayer.offsetWidth;

            window.requestAnimationFrame(() => {
                outgoingLayer.classList.add("is-leaving");
                outgoingLayer.classList.remove("is-active");
                incomingLayer.classList.add("is-active");
            });

            currentLayer = incomingLayer;
            nextLayer = outgoingLayer;
            activeIndex = index;

            cleanupLeavingLayer();
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                window.clearTimeout(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function queueAutoplay() {
            stopAutoplay();

            if (manualSelection || thumbs.length <= 1) {
                return;
            }

            autoplayTimer = window.setTimeout(() => {
                renderImage((activeIndex + 1) % thumbs.length, { animate: true });
                queueAutoplay();
            }, autoplayDelay);
        }

        function pauseOnManualSelection() {
            manualSelection = true;
            stopAutoplay();
        }

        function resumeAutoplay() {
            if (!manualSelection) {
                return;
            }

            manualSelection = false;
            queueAutoplay();
        }

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener("click", (event) => {
                event.preventDefault();
                pauseOnManualSelection();
                renderImage(index, { animate: true });
            });
        });

        document.addEventListener("click", (event) => {
            if (!manualSelection) {
                return;
            }

            if (event.target.closest("[data-gallery-thumb]")) {
                return;
            }

            resumeAutoplay();
        });

        renderImage(activeIndex, { animate: false });
        queueAutoplay();
    });

});
