---
name: Estado responsive Home - Hero y Hamburger Menu
description: Trabajo de responsive mobile completado en el Home: Hero (100dvh) + menú hamburguesa con panel slide-in
type: project
---

Fase 1 de responsive completada para el Home (home.blade.php).

**Cambios realizados:**
- `resources/views/web/home.blade.php`: Added `id="mainNav"` to header, hamburger button (#hamburgerBtn), mobile nav panel (#navLinks with close #navClose), overlay (#navOverlay) — all after hero section closing tag.
- `resources/css/web/components/header.css`: Added global styles for .hamburger, .nav-mobile, .nav-overlay, .nav-mobile__nav, .nav-mobile__cta; added @media (max-width: 768px) block that hides desktop .nav, shows .hamburger, .nav-mobile, .nav-overlay, sets header to 70px row.
- `resources/css/web/sections/home-hero.css`: Added @media (max-width: 768px) block with height: 100dvh, margin-top: -70px (cancels JS body padding), centered content, h1 28px, p 15px, button min-height 48px.

**Why:** margin-top: -70px on .hero corrects the body.paddingTop=70px the JS (web.js applyMobileFixed) adds when making #mainNav position:fixed on ≤1024px. Without this, the hero would start 70px below viewport top.

**Key breakpoints used:** @media (max-width: 768px) for mobile-only. Existing @media (max-width: 900px) kept untouched (tablet).

**Pending:** Other pages (about, products, etc.) still need the hamburger menu HTML added (future phases). Currently only home.blade.php has it.

**How to apply:** When working on other slugs, remember to replicate the hamburger HTML in each page's header section. The CSS is already global in header.css.
