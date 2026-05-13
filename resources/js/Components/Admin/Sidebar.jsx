import { router, usePage } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useEffect, useRef, useState } from "react";

const ADMIN_LOGO_PATH = "/images/admin/logo.png";
const ADMIN_SIDEBAR_SCROLL_KEY = "admin-sidebar-scroll-top";
const ADMIN_SIDEBAR_OPEN_KEY = "admin-sidebar-open-sections";

function getBrandInitials(name) {
    const initials = (name || "Tarea SRL")
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0))
        .join("")
        .toUpperCase();

    return initials || "TS";
}

function isItemActive(item, currentUrl) {
    if (item.exact) {
        return currentUrl === item.url;
    }

    if (item.match) {
        return currentUrl.startsWith(item.match);
    }

    return currentUrl.startsWith(item.url);
}

function buildOpenSections(sections, currentUrl) {
    return sections.reduce((state, section) => {
        state[section.key] = section.children.some((item) =>
            isItemActive(item, currentUrl),
        );

        return state;
    }, {});
}

function getStoredOpenSections(sections, currentUrl) {
    const defaults = buildOpenSections(sections, currentUrl);

    if (typeof window === "undefined") {
        return defaults;
    }

    try {
        const savedValue = window.sessionStorage.getItem(ADMIN_SIDEBAR_OPEN_KEY);

        if (!savedValue) {
            return defaults;
        }

        const parsedValue = JSON.parse(savedValue);

        if (!parsedValue || typeof parsedValue !== "object") {
            return defaults;
        }

        return sections.reduce((state, section) => {
            state[section.key] = Boolean(
                parsedValue[section.key] ?? defaults[section.key],
            );

            return state;
        }, {});
    } catch {
        return defaults;
    }
}

function SidebarItem({
    item,
    currentUrl,
    onNavigate,
    isVisible = true,
    delay = 0,
}) {
    const active = isItemActive(item, currentUrl);

    return (
        <button
            type="button"
            onClick={() => onNavigate(item.url)}
            style={{ transitionDelay: isVisible ? `${delay}ms` : "0ms" }}
            className={`group flex w-full translate-x-0 items-center gap-3 rounded-2xl px-3 py-2.5 text-left transition-all duration-300 ${
                isVisible ? "opacity-100" : "translate-x-2 opacity-0"
            } ${
                active
                    ? "bg-[#25A7CA]/12 text-[#117a98] shadow-[0_12px_28px_rgba(37,167,202,0.14)]"
                    : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
            }`}
        >
            <span
                className={`h-2 w-2 rounded-full transition ${
                    active ? "bg-[#25A7CA]" : "bg-slate-300 group-hover:bg-slate-500"
                }`}
            />
            <span className="min-w-0 flex-1 truncate text-sm font-medium">
                {item.title}
            </span>
            {item.badge ? (
                <span className="rounded-full bg-[#25A7CA] px-2 py-0.5 text-[11px] font-semibold text-white">
                    {item.badge}
                </span>
            ) : null}
        </button>
    );
}

function SidebarSection({
    section,
    currentUrl,
    onNavigate,
    isOpen,
    onToggle,
}) {
    const hasActiveChild = section.children.some((item) =>
        isItemActive(item, currentUrl),
    );
    const sectionId = `sidebar-section-${section.key}`;

    return (
        <section
            className={`overflow-hidden rounded-[24px] border p-3 transition-all duration-300 ${
                section.highlighted
                    ? "border-[#25A7CA]/25 bg-[linear-gradient(180deg,rgba(37,167,202,0.1),rgba(255,255,255,0.95))]"
                    : "border-slate-200 bg-white"
            } ${
                hasActiveChild
                    ? "shadow-[0_18px_40px_rgba(15,23,42,0.08)]"
                    : "hover:border-slate-300"
            }`}
        >
            <button
                type="button"
                onClick={() => onToggle(section.key)}
                aria-expanded={isOpen}
                aria-controls={sectionId}
                className="flex w-full items-center gap-3 rounded-[20px] px-1 py-1.5 text-left"
            >
                <div
                    className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl transition-all duration-300 ${
                        hasActiveChild || section.highlighted
                            ? "bg-[#25A7CA] text-white shadow-[0_16px_28px_rgba(37,167,202,0.28)]"
                            : "bg-slate-100 text-slate-500"
                    }`}
                >
                    <Icon icon={section.icon} width={18} />
                </div>

                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-semibold text-slate-900">
                        {section.title}
                    </p>
                    {section.description ? (
                        <p className="truncate text-xs text-slate-500">
                            {section.description}
                        </p>
                    ) : null}
                </div>

                <div
                    className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border transition-all duration-300 ${
                        isOpen
                            ? "rotate-180 border-[#25A7CA]/20 bg-[#25A7CA]/10 text-[#117a98]"
                            : "border-slate-200 bg-white text-slate-400"
                    }`}
                >
                    <Icon icon="solar:alt-arrow-down-outline" width={16} />
                </div>
            </button>

            <div
                id={sectionId}
                className={`grid transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] ${
                    isOpen ? "mt-2 grid-rows-[1fr] opacity-100" : "grid-rows-[0fr] opacity-70"
                }`}
            >
                <div className="overflow-hidden">
                    <div
                        className={`space-y-1 border-t border-slate-200/80 pt-3 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] ${
                            isOpen ? "translate-y-0 opacity-100" : "-translate-y-2 opacity-0"
                        }`}
                    >
                        {section.children.map((item, index) => (
                            <SidebarItem
                                key={item.url}
                                item={item}
                                currentUrl={currentUrl}
                                onNavigate={onNavigate}
                                isVisible={isOpen}
                                delay={index * 35}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

export default function Sidebar({
    onNavigateComplete = () => {},
    mobile = false,
}) {
    const page = usePage();
    const auth = page.props?.auth;
    const app = page.props?.app;
    const currentUrl = page.url ?? "";
    const unreadSubmissions = page.props?.admin?.unreadSubmissionsCount ?? 0;
    const [showLogo, setShowLogo] = useState(true);
    const navRef = useRef(null);

    const dashboardSection = {
        key: "overview",
        title: "Resumen",
        description: "Vista rápida del panel",
        icon: "solar:widget-2-outline",
        children: [
            {
                title: "Ver resumen",
                url: "/admin/dashboard",
                match: "/admin/dashboard",
            },
        ],
    };

    const siteSections = [
        {
            key: "home",
            title: "Inicio",
            description: "Portada y bloques principales",
            icon: "solar:home-smile-outline",
            children: [
                {
                    title: "Banners principales",
                    url: "/admin/home/sliders",
                    match: "/admin/home/sliders",
                },
                {
                    title: "Bloque sobre la empresa",
                    url: "/admin/home/about",
                    match: "/admin/home/about",
                },
                {
                    title: "Marcas destacadas",
                    url: "/admin/home/representatives",
                    match: "/admin/home/representatives",
                },
            ],
        },
        {
            key: "about",
            title: "Sobre la empresa",
            description: "Información institucional",
            icon: "solar:users-group-rounded-outline",
            children: [
                {
                    title: "Página sobre la empresa",
                    url: "/admin/about",
                    match: "/admin/about",
                },
            ],
        },
        {
            key: "products",
            title: "Productos",
            description: "Catálogo y organización",
            icon: "solar:box-outline",
            children: [
                {
                    title: "Grupos de productos",
                    url: "/admin/products?tab=families",
                    exact: true,
                },
                {
                    title: "Subgrupos de productos",
                    url: "/admin/products?tab=subfamilies",
                    exact: true,
                },
                {
                    title: "Listado de productos",
                    url: "/admin/products?tab=products",
                    exact: true,
                },
                {
                    title: "Carga por archivo",
                    url: "/admin/products?tab=import",
                    exact: true,
                },
            ],
        },
        {
            key: "catalog",
            title: "Catálogo",
            description: "Descarga y pedidos",
            icon: "solar:document-text-outline",
            children: [
                {
                    title: "Página del catálogo",
                    url: "/admin/catalog?tab=page",
                    exact: true,
                },
                {
                    title: "Pedidos de catálogo",
                    url: "/admin/catalog?tab=requests",
                    exact: true,
                },
            ],
        },
        {
            key: "quality",
            title: "Calidad",
            description: "Certificados y contenidos",
            icon: "solar:shield-check-outline",
            children: [
                {
                    title: "Página de calidad",
                    url: "/admin/quality",
                    match: "/admin/quality",
                },
            ],
        },
        {
            key: "clients",
            title: "Clientes",
            description: "Rubros y logos",
            icon: "solar:buildings-2-outline",
            children: [
                {
                    title: "Página de clientes",
                    url: "/admin/clients?tab=page",
                    exact: true,
                },
                {
                    title: "Rubros de clientes",
                    url: "/admin/clients?tab=categories",
                    exact: true,
                },
                {
                    title: "Listado de clientes",
                    url: "/admin/clients?tab=clients",
                    exact: true,
                },
            ],
        },
        {
            key: "partners",
            title: "Marcas aliadas",
            description: "Marcas y socios comerciales",
            icon: "solar:users-group-two-rounded-outline",
            children: [
                {
                    title: "Página de marcas",
                    url: "/admin/representadas?tab=page",
                    exact: true,
                },
                {
                    title: "Listado de marcas",
                    url: "/admin/representadas?tab=representadas",
                    exact: true,
                },
            ],
        },
        {
            key: "news",
            title: "Novedades",
            description: "Noticias y publicaciones",
            icon: "solar:document-text-outline",
            children: [
                {
                    title: "Temas",
                    url: "/admin/novedades?tab=categorias",
                    exact: true,
                },
                {
                    title: "Publicaciones",
                    url: "/admin/novedades?tab=novedades",
                    exact: true,
                },
            ],
        },
        {
            key: "contact",
            title: "Contacto",
            description: "Datos y mensajes",
            icon: "solar:chat-round-outline",
            children: [
                {
                    title: "Página de contacto",
                    url: "/admin/contact?tab=page",
                    exact: true,
                },
                {
                    title: "Mensajes recibidos",
                    url: "/admin/contact?tab=requests",
                    exact: true,
                    badge: unreadSubmissions > 0 ? unreadSubmissions : undefined,
                },
            ],
        },
    ];

    const budgetSection = {
        key: "quotes",
        title: "Presupuestos",
        description: "Formulario y pedidos",
        icon: "solar:document-text-outline",
        highlighted: true,
        children: [
            {
                title: "Página de presupuestos",
                url: "/admin/quote?tab=page",
                exact: true,
            },
            {
                title: "Pedidos de presupuesto",
                url: "/admin/quote?tab=requests",
                exact: true,
            },
        ],
    };

    const whatsappSection = {
        key: "whatsapp",
        title: "Botón de WhatsApp",
        description: "Acceso directo en la web",
        icon: "solar:phone-calling-rounded-outline",
        children: [
            {
                title: "Editar botón de WhatsApp",
                url: "/admin/whatsapp",
                match: "/admin/whatsapp",
            },
        ],
    };

    const extrasSections = [
        {
            key: "newsletter",
            title: "Correos a suscriptores",
            description: "Lista de personas y envíos",
            icon: "solar:letter-outline",
            children: [
                {
                    title: "Personas suscritas",
                    url: "/admin/newsletter",
                    match: "/admin/newsletter",
                },
            ],
        },
    ];

    const seoSection = {
        key: "search",
        title: "Cómo se ve tu web en Google",
        description: "Título, imagen y texto al compartir",
        icon: "solar:chart-2-outline",
        children: [
            {
                title: "Editar vista en Google y redes",
                url: "/admin/seo",
                match: "/admin/seo",
            },
        ],
    };

    const usersSection = {
        key: "users",
        title: "Personas con acceso",
        description: "Quién puede entrar al panel",
        icon: "solar:users-group-rounded-outline",
        children: [
            {
                title: "Administrar accesos",
                url: "/admin/users",
                match: "/admin/users",
            },
        ],
    };

    const allSections = [
        dashboardSection,
        ...siteSections,
        budgetSection,
        whatsappSection,
        ...extrasSections,
        seoSection,
        usersSection,
    ];

    const [openSections, setOpenSections] = useState(() =>
        getStoredOpenSections(allSections, currentUrl),
    );

    useEffect(() => {
        const element = navRef.current;
        if (!element) {
            return;
        }

        const savedScrollTop = window.sessionStorage.getItem(
            ADMIN_SIDEBAR_SCROLL_KEY,
        );

        if (savedScrollTop !== null) {
            window.requestAnimationFrame(() => {
                element.scrollTop = Number(savedScrollTop) || 0;
            });
        }
    }, [currentUrl]);

    useEffect(() => {
        const element = navRef.current;
        if (!element) {
            return undefined;
        }

        const handleScroll = () => {
            window.sessionStorage.setItem(
                ADMIN_SIDEBAR_SCROLL_KEY,
                String(element.scrollTop),
            );
        };

        element.addEventListener("scroll", handleScroll, { passive: true });

        return () => {
            element.removeEventListener("scroll", handleScroll);
        };
    }, []);

    useEffect(() => {
        const activeSections = buildOpenSections(allSections, currentUrl);

        setOpenSections((current) => {
            let changed = false;
            const nextState = { ...current };

            Object.entries(activeSections).forEach(([key, shouldOpen]) => {
                if (shouldOpen && !nextState[key]) {
                    nextState[key] = true;
                    changed = true;
                }
            });

            return changed ? nextState : current;
        });
    }, [currentUrl]);

    useEffect(() => {
        if (typeof window === "undefined") {
            return;
        }

        window.sessionStorage.setItem(
            ADMIN_SIDEBAR_OPEN_KEY,
            JSON.stringify(openSections),
        );
    }, [openSections]);

    const handleNavigate = (url) => {
        if (url === currentUrl) {
            onNavigateComplete();
            return;
        }

        if (navRef.current) {
            window.sessionStorage.setItem(
                ADMIN_SIDEBAR_SCROLL_KEY,
                String(navRef.current.scrollTop),
            );
        }

        onNavigateComplete();
        router.visit(url, {
            preserveScroll: true,
            onSuccess: () => {
                const element = navRef.current;
                const savedScrollTop = window.sessionStorage.getItem(
                    ADMIN_SIDEBAR_SCROLL_KEY,
                );

                if (element && savedScrollTop !== null) {
                    window.requestAnimationFrame(() => {
                        element.scrollTop = Number(savedScrollTop) || 0;
                    });
                }
            },
        });
    };

    const toggleSection = (sectionKey) => {
        setOpenSections((current) => ({
            ...current,
            [sectionKey]: !current[sectionKey],
        }));
    };

    const brandName = app?.name ?? "Tarea SRL";
    const user = auth?.user;

    return (
        <aside
            className={
                mobile
                    ? "flex h-full w-full flex-col border-r border-slate-200 bg-white text-slate-900"
                    : "sticky top-0 flex h-screen w-[320px] flex-col border-r border-slate-200 bg-white text-slate-900"
            }
        >
            <div className="border-b border-slate-200 px-5 py-5">
                <div className="flex items-start gap-3">
                    <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-[18px] border border-slate-200 bg-white shadow-[0_10px_30px_rgba(15,23,42,0.08)]">
                        {showLogo ? (
                            <img
                                src={ADMIN_LOGO_PATH}
                                alt={`Logo de ${brandName}`}
                                className="h-10 w-10 object-contain"
                                onError={() => setShowLogo(false)}
                            />
                        ) : (
                            <span className="text-lg font-semibold tracking-[0.18em] text-slate-700">
                                {getBrandInitials(brandName)}
                            </span>
                        )}
                    </div>

                    <div className="min-w-0 pt-1">
                        <p className="truncate text-base font-semibold text-slate-900">
                            {brandName}
                        </p>
                        <p className="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">
                            Menú principal
                        </p>
                    </div>

                    {mobile ? (
                        <button
                            type="button"
                            onClick={onNavigateComplete}
                            className="ml-auto flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                            aria-label="Cerrar menú"
                        >
                            <Icon icon="solar:close-circle-outline" width={18} />
                        </button>
                    ) : null}
                </div>
            </div>

            <nav
                ref={navRef}
                className="flex-1 space-y-6 overflow-y-auto px-4 py-5"
            >
                <div className="space-y-3">
                    <SidebarSection
                        section={dashboardSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        isOpen={Boolean(openSections[dashboardSection.key])}
                        onToggle={toggleSection}
                    />
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Contenido del sitio
                        </p>
                    </div>

                    {siteSections.map((section) => (
                        <SidebarSection
                            key={section.key}
                            section={section}
                            currentUrl={currentUrl}
                            onNavigate={handleNavigate}
                            isOpen={Boolean(openSections[section.key])}
                            onToggle={toggleSection}
                        />
                    ))}

                    <SidebarSection
                        section={budgetSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        isOpen={Boolean(openSections[budgetSection.key])}
                        onToggle={toggleSection}
                    />

                    <SidebarSection
                        section={whatsappSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        isOpen={Boolean(openSections[whatsappSection.key])}
                        onToggle={toggleSection}
                    />
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Mensajes y envíos
                        </p>
                    </div>

                    {extrasSections.map((section) => (
                        <SidebarSection
                            key={section.key}
                            section={section}
                            currentUrl={currentUrl}
                            onNavigate={handleNavigate}
                            isOpen={Boolean(openSections[section.key])}
                            onToggle={toggleSection}
                        />
                    ))}
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Ajustes generales
                        </p>
                    </div>

                    <SidebarSection
                        section={seoSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        isOpen={Boolean(openSections[seoSection.key])}
                        onToggle={toggleSection}
                    />

                    <SidebarSection
                        section={usersSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        isOpen={Boolean(openSections[usersSection.key])}
                        onToggle={toggleSection}
                    />
                </div>
            </nav>

            <div className="border-t border-slate-200 p-4">
                {user ? (
                    <div className="mb-3 flex items-center gap-3 rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                        {user.avatar_url ? (
                            <img
                                src={user.avatar_url}
                                alt={user.name}
                                className="h-11 w-11 rounded-full object-cover"
                            />
                        ) : (
                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#25A7CA] text-sm font-semibold text-white">
                                {user.name?.charAt(0)?.toUpperCase?.() || "A"}
                            </div>
                        )}
                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold text-slate-900">
                                {user.name}
                            </p>
                            <p className="truncate text-xs text-slate-500">
                                {user.email}
                            </p>
                        </div>
                    </div>
                ) : null}

                <button
                    type="button"
                    onClick={() => router.post("/admin/logout")}
                    className="flex w-full items-center justify-center gap-2 rounded-[20px] border border-red-200 bg-red-50 py-3 text-sm font-medium text-red-600 transition hover:bg-red-100"
                >
                    <Icon icon="solar:logout-2-outline" width={16} />
                    Cerrar sesión
                </button>
            </div>
        </aside>
    );
}
