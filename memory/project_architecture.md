---
name: Arquitectura del proyecto Tarea-SRL
description: Laravel + Blade + Vite. Header repetido en cada página (no es un partial compartido). CSS en resources/css/web/.
type: project
---

Laravel Blade + Vite project. Web CSS entry: resources/css/web/app.css (imports all partials).

**Critical architecture note:** The site header HTML is duplicated in EVERY page's blade template (home, about, products, etc.) — it is NOT in a shared partial/layout. The layout (resources/views/web/layouts/app.blade.php) only wraps the head/body tags.

**CSS organization:** resources/css/web/components/ (header, footer), resources/css/web/sections/ (page-specific), resources/css/web/base/ (variables, reset, base).

**JS:** resources/js/web.js — handles hamburger menu (requires #hamburgerBtn, #navLinks, #navClose, #navOverlay), hero slider, mobile nav fixed positioning (#mainNav gets position:fixed at ≤1024px via applyMobileFixed()).

**Colors:** Primary #093E66, secondary #035183, text #1A181C, border #DBDADA.
**Fonts:** Montserrat (primary), Raleway (secondary).

**How to apply:** When modifying any page, always check if the header in that page has the hamburger elements. For CSS changes: always use @media (max-width: 768px) for mobile, never touch desktop styles.
