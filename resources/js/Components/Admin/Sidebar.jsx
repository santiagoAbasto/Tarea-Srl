import { router, usePage } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useEffect, useRef, useState } from "react";

const ADMIN_LOGO_PATH = "/images/admin/logo.png";
const ADMIN_SIDEBAR_SCROLL_KEY = "admin-sidebar-scroll-top";

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

function SidebarItem({ item, currentUrl, onNavigate }) {
    const active = isItemActive(item, currentUrl);

    return (
        <button
            type="button"
            onClick={() => onNavigate(item.url)}
            className={`group flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left transition ${
                active
                    ? "bg-[#25A7CA]/12 text-[#117a98]"
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

function SidebarSection({ section, currentUrl, onNavigate, highlighted = false }) {
    const hasActiveChild = section.children.some((item) =>
        isItemActive(item, currentUrl),
    );

    return (
        <section
            className={`rounded-[24px] border p-3 ${
                highlighted
                    ? "border-[#25A7CA]/25 bg-[#25A7CA]/8"
                    : "border-slate-200 bg-white"
            }`}
        >
            <div className="flex items-center gap-3 px-1 py-1.5">
                <div
                    className={`flex h-10 w-10 items-center justify-center rounded-2xl ${
                        hasActiveChild || highlighted
                            ? "bg-[#25A7CA] text-white"
                            : "bg-slate-100 text-slate-500"
                    }`}
                >
                    <Icon icon={section.icon} width={18} />
                </div>
                <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-slate-900">
                        {section.title}
                    </p>
                    {section.description ? (
                        <p className="truncate text-xs text-slate-500">
                            {section.description}
                        </p>
                    ) : null}
                </div>
            </div>

            <div className="mt-2 space-y-1">
                {section.children.map((item) => (
                    <SidebarItem
                        key={item.url}
                        item={item}
                        currentUrl={currentUrl}
                        onNavigate={onNavigate}
                    />
                ))}
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

    const dashboardItem = {
        title: "Dashboard",
        description: "Resumen general del panel",
        icon: "solar:widget-2-outline",
        url: "/admin/dashboard",
        match: "/admin/dashboard",
    };

    const siteSections = [
        {
            title: "Home",
            description: "Inicio y bloques principales",
            icon: "solar:home-smile-outline",
            children: [
                {
                    title: "Sliders",
                    url: "/admin/home/sliders",
                    match: "/admin/home/sliders",
                },
                {
                    title: "Home Nosotros",
                    url: "/admin/home/about",
                    match: "/admin/home/about",
                },
                {
                    title: "Representantes exclusivos",
                    url: "/admin/home/representatives",
                    match: "/admin/home/representatives",
                },
            ],
        },
        {
            title: "Nosotros",
            description: "Contenido institucional",
            icon: "solar:users-group-rounded-outline",
            children: [
                {
                    title: "Pagina Nosotros",
                    url: "/admin/about",
                    match: "/admin/about",
                },
            ],
        },
        {
            title: "Productos",
            description: "Catalogo comercial",
            icon: "solar:box-outline",
            children: [
                {
                    title: "Familias",
                    url: "/admin/products?tab=families",
                    exact: true,
                },
                {
                    title: "Subfamilias",
                    url: "/admin/products?tab=subfamilies",
                    exact: true,
                },
                {
                    title: "Productos",
                    url: "/admin/products?tab=products",
                    exact: true,
                },
                {
                    title: "Importador masivo",
                    url: "/admin/products?tab=import",
                    exact: true,
                },
            ],
        },
        {
            title: "Catalogo",
            description: "Descarga y pedidos",
            icon: "solar:document-text-outline",
            children: [
                {
                    title: "Pagina de catalogo",
                    url: "/admin/catalog?tab=page",
                    exact: true,
                },
                {
                    title: "Solicitudes de catalogo",
                    url: "/admin/catalog?tab=requests",
                    exact: true,
                },
            ],
        },
        {
            title: "Calidad",
            description: "Certificaciones y contenido",
            icon: "solar:shield-check-outline",
            children: [
                {
                    title: "Pagina de calidad",
                    url: "/admin/quality",
                    match: "/admin/quality",
                },
            ],
        },
        {
            title: "Clientes",
            description: "Rubros y logos",
            icon: "solar:buildings-2-outline",
            children: [
                {
                    title: "Pagina de clientes",
                    url: "/admin/clients",
                    match: "/admin/clients",
                },
                {
                    title: "Categorias de clientes",
                    url: "/admin/clients?tab=categories",
                    exact: true,
                },
                {
                    title: "Clientes",
                    url: "/admin/clients?tab=clients",
                    exact: true,
                },
            ],
        },
        {
            title: "Representadas",
            description: "Marcas y aliados",
            icon: "solar:users-group-two-rounded-outline",
            children: [
                {
                    title: "Página de representadas",
                    url: "/admin/representadas?tab=page",
                    exact: true,
                },
                {
                    title: "Representadas",
                    url: "/admin/representadas?tab=representadas",
                    exact: true,
                },
            ],
        },
        {
            title: "Novedades",
            description: "Noticias del sitio",
            icon: "solar:document-text-outline",
            children: [
                {
                    title: "Categorias",
                    url: "/admin/novedades?tab=categorias",
                    exact: true,
                },
                {
                    title: "Novedades",
                    url: "/admin/novedades?tab=novedades",
                    exact: true,
                },
            ],
        },
        {
            title: "Contacto",
            description: "Datos y consultas",
            icon: "solar:chat-round-outline",
            children: [
                {
                    title: "Página de contacto",
                    url: "/admin/contact?tab=page",
                    exact: true,
                },
                {
                    title: "Consultas recibidas",
                    url: "/admin/contact?tab=requests",
                    exact: true,
                    badge: unreadSubmissions > 0 ? unreadSubmissions : undefined,
                },
            ],
        },
    ];

    const budgetSection = {
        title: "Presupuesto",
        description: "Formulario y solicitudes",
        icon: "solar:document-text-outline",
        children: [
            {
                title: "Pagina de presupuesto",
                url: "/admin/quote?tab=page",
                exact: true,
            },
            {
                title: "Solicitudes de presupuesto",
                url: "/admin/quote?tab=requests",
                exact: true,
            },
        ],
    };

    const whatsappSection = {
        title: "WhatsApp flotante",
        description: "Botón flotante en la web",
        icon: "solar:phone-calling-rounded-outline",
        children: [
            {
                title: "Editar WhatsApp",
                url: "/admin/whatsapp",
                match: "/admin/whatsapp",
            },
        ],
    };

    const extrasSections = [
        {
            title: "Newsletter",
            description: "Suscriptores y campañas",
            icon: "solar:letter-outline",
            children: [
                {
                    title: "Suscriptores",
                    url: "/admin/newsletter",
                    match: "/admin/newsletter",
                },
            ],
        },
    ];

    const seoSection = {
        title: "SEO de la web",
        description: "Google, Whatsapp y redes sociales",
        icon: "solar:chart-2-outline",
        children: [
            {
                title: "Configurar SEO",
                url: "/admin/seo",
                match: "/admin/seo",
            },
        ],
    };

    const usersSection = {
        title: "Usuarios",
        description: "Accesos y perfiles del panel",
        icon: "solar:users-group-rounded-outline",
        children: [
            {
                title: "Gestionar usuarios",
                url: "/admin/users",
                match: "/admin/users",
            },
        ],
    };

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
                            <span className="text-lg font-semibold tracking-[0.18em] text-white">
                                {getBrandInitials(brandName)}
                            </span>
                        )}
                    </div>

                    <div className="min-w-0 pt-1">
                        <p className="truncate text-base font-semibold text-slate-900">
                            {brandName}
                        </p>
                        <p className="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">
                            Navegacion del sitio
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
                        section={{
                            title: dashboardItem.title,
                            description: dashboardItem.description,
                            icon: dashboardItem.icon,
                            children: [
                                {
                                    title: "Ir al dashboard",
                                    url: dashboardItem.url,
                                    match: dashboardItem.match,
                                },
                            ],
                        }}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                    />
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Web
                        </p>
                    </div>

                    {siteSections.map((section) => (
                        <SidebarSection
                            key={section.title}
                            section={section}
                            currentUrl={currentUrl}
                            onNavigate={handleNavigate}
                        />
                    ))}

                    <SidebarSection
                        section={budgetSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                        highlighted
                    />

                    <SidebarSection
                        section={whatsappSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                    />
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Extras
                        </p>
                    </div>

                    {extrasSections.map((section) => (
                        <SidebarSection
                            key={section.title}
                            section={section}
                            currentUrl={currentUrl}
                            onNavigate={handleNavigate}
                        />
                    ))}
                </div>

                <div className="space-y-3">
                    <div className="px-1">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Metadatos
                        </p>
                    </div>

                    <SidebarSection
                        section={seoSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
                    />

                    <SidebarSection
                        section={usersSection}
                        currentUrl={currentUrl}
                        onNavigate={handleNavigate}
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
