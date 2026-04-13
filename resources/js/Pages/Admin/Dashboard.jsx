import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";
import { Head, router, usePage } from "@inertiajs/react";

function StatCard({ label, value, helper, icon, tone = "sky" }) {
    const tones = {
        sky: "bg-sky-50 text-sky-600",
        emerald: "bg-emerald-50 text-emerald-600",
        amber: "bg-amber-50 text-amber-600",
        rose: "bg-rose-50 text-rose-600",
        violet: "bg-violet-50 text-violet-600",
        slate: "bg-slate-100 text-slate-600",
    };

    return (
        <article className="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div className={`mb-4 flex h-12 w-12 items-center justify-center rounded-2xl ${tones[tone] ?? tones.sky}`}>
                <Icon icon={icon} width={20} />
            </div>
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{value}</p>
            {helper ? <p className="mt-2 text-xs leading-5 text-slate-400">{helper}</p> : null}
        </article>
    );
}

function PendingCard({ item }) {
    const tones = {
        amber: "border-amber-200 bg-amber-50 text-amber-700",
        rose: "border-rose-200 bg-rose-50 text-rose-700",
        sky: "border-sky-200 bg-sky-50 text-sky-700",
        emerald: "border-emerald-200 bg-emerald-50 text-emerald-700",
    };

    return (
        <button
            type="button"
            onClick={() => router.visit(item.url)}
            className="flex w-full items-center gap-4 rounded-[24px] border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-[#25A7CA]/35 hover:shadow-md"
        >
            <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border ${tones[item.tone] ?? tones.sky}`}>
                <Icon icon={item.icon} width={18} />
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-slate-900">{item.title}</p>
                <p className="mt-1 text-xs text-slate-500">{item.description}</p>
            </div>
            <div className="shrink-0 rounded-full bg-slate-900 px-3 py-1 text-sm font-semibold text-white">
                {item.count}
            </div>
        </button>
    );
}

export default function Dashboard({ stats, quickLinks = [], inbox, seo, pendingItems = [], recentItems = [] }) {
    const page = usePage();
    const { app } = page.props;
    const groupedQuickLinks = quickLinks.reduce((acc, item) => {
        const group = item.group || "General";
        acc[group] = acc[group] || [];
        acc[group].push(item);
        return acc;
    }, {});

    return (
        <AdminLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.22),_transparent_36%),radial-gradient(circle_at_85%_15%,_rgba(15,23,42,0.18),_transparent_24%),linear-gradient(135deg,#ffffff_0%,#f3f7fb_45%,#edf6fa_100%)] px-6 py-8 md:px-8">
                        <div className="grid gap-8 xl:grid-cols-[minmax(0,1fr)_340px]">
                            <div className="max-w-3xl">
                                <div className="inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/15 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[#117a98] shadow-sm">
                                    <Icon icon="solar:widget-2-outline" width={14} />
                                    Panel principal
                                </div>

                                <h1 className="mt-4 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    {app?.name ?? "Tarea SRL"}
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Un resumen rápido del estado comercial, contenido cargado y accesos principales del panel para trabajar la web sin perder tiempo navegando módulo por módulo.
                                </p>

                                <div className="mt-5 flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onClick={() => router.visit("/admin/seo")}
                                        className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                                    >
                                        <Icon icon="solar:chart-2-outline" width={16} />
                                        Revisar metadatos
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => router.visit("/admin/contact?tab=requests")}
                                        className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA]/40 hover:text-slate-900"
                                    >
                                        <Icon icon="solar:inbox-outline" width={16} />
                                        Ver pendientes
                                    </button>
                                </div>
                            </div>

                            <div className="rounded-[28px] border border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur">
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            SEO de la web
                                        </p>
                                        <p className="mt-2 text-3xl font-semibold text-slate-900">
                                            {seo?.completion ?? 0}%
                                        </p>
                                    </div>
                                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <Icon icon="solar:graph-up-outline" width={20} />
                                    </div>
                                </div>

                                <p className="mt-2 text-sm text-slate-500">
                                    {seo?.configured ?? 0} de {seo?.total ?? 0} secciones completas.
                                </p>
                                <div className="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        className="h-full rounded-full bg-emerald-500 transition-all"
                                        style={{ width: `${seo?.completion ?? 0}%` }}
                                    />
                                </div>

                                <div className="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div className="rounded-2xl bg-slate-50 px-4 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Sin leer</p>
                                        <p className="mt-1 text-xl font-semibold text-slate-900">
                                            {(inbox?.contacts_unread ?? 0) + (inbox?.quotes_unread ?? 0) + (inbox?.catalog_unread ?? 0)}
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-slate-50 px-4 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Slides activos</p>
                                        <p className="mt-1 text-xl font-semibold text-slate-900">{stats?.active_home_slides ?? 0}</p>
                                    </div>
                                    <div className="rounded-2xl bg-slate-50 px-4 py-3">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Usuarios admin</p>
                                        <p className="mt-1 text-xl font-semibold text-slate-900">{stats?.users_with_access ?? 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Productos"
                        value={stats?.products ?? 0}
                        helper={`${stats?.families ?? 0} familias cargadas`}
                        icon="solar:box-outline"
                        tone="sky"
                    />
                    <StatCard
                        label="Novedades"
                        value={stats?.posts ?? 0}
                        helper="Publicaciones disponibles para la web"
                        icon="solar:document-text-outline"
                        tone="violet"
                    />
                    <StatCard
                        label="Clientes y marcas"
                        value={(stats?.clients ?? 0) + (stats?.partners ?? 0)}
                        helper={`${stats?.clients ?? 0} clientes y ${stats?.partners ?? 0} representadas`}
                        icon="solar:users-group-two-rounded-outline"
                        tone="emerald"
                    />
                    <StatCard
                        label="Base comercial"
                        value={(stats?.contacts ?? 0) + (stats?.quotes ?? 0) + (stats?.catalog_requests ?? 0)}
                        helper="Contactos, presupuestos y solicitudes"
                        icon="solar:inbox-outline"
                        tone="amber"
                    />
                </section>

                <section className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <div className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">Accesos por área</h2>
                            <p className="mt-2 max-w-2xl text-sm text-slate-500">
                                Entradas rápidas a los módulos más usados, agrupadas según el tipo de tarea.
                            </p>
                        </div>

                        <div className="mt-6 grid gap-6 lg:grid-cols-2">
                            {Object.entries(groupedQuickLinks).map(([group, items]) => (
                                <div key={group} className="space-y-3">
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                        {group}
                                    </p>
                                    {items.map((link) => (
                                        <button
                                            key={link.url}
                                            type="button"
                                            onClick={() => router.visit(link.url)}
                                            className="flex w-full items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-left transition hover:border-[#25A7CA]/35 hover:bg-white"
                                        >
                                            <div className="flex min-w-0 gap-3">
                                                <div className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-[#117a98] shadow-sm">
                                                    <Icon icon={link.icon} width={18} />
                                                </div>
                                                <div className="min-w-0">
                                                    <p className="text-sm font-semibold text-slate-900">{link.title}</p>
                                                    <p className="mt-1 text-xs text-slate-500">{link.description}</p>
                                                </div>
                                            </div>
                                            <Icon icon="solar:arrow-right-up-outline" width={18} className="mt-0.5 shrink-0 text-slate-400" />
                                        </button>
                                    ))}
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-6">
                        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    <h2 className="text-xl font-semibold text-slate-900">Pendientes</h2>
                                    <p className="mt-2 text-sm text-slate-500">
                                        Lo que conviene revisar primero.
                                    </p>
                                </div>
                                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                    <Icon icon="solar:bell-bing-outline" width={20} />
                                </div>
                            </div>

                            <div className="mt-5 space-y-3">
                                {pendingItems.length > 0 ? (
                                    pendingItems.map((item) => <PendingCard key={item.title} item={item} />)
                                ) : (
                                    <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                                        No hay pendientes urgentes. El panel está al día.
                                    </div>
                                )}
                            </div>
                        </section>

                        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    <h2 className="text-xl font-semibold text-slate-900">Actividad reciente</h2>
                                    <p className="mt-2 text-sm text-slate-500">
                                        Últimos movimientos relevantes dentro del sitio.
                                    </p>
                                </div>
                                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                                    <Icon icon="solar:history-outline" width={20} />
                                </div>
                            </div>

                            <div className="mt-5 space-y-3">
                                {recentItems.length > 0 ? (
                                    recentItems.map((item, index) => (
                                        <button
                                            key={`${item.type}-${item.title}-${index}`}
                                            type="button"
                                            onClick={() => router.visit(item.url)}
                                            className="flex w-full items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-left transition hover:border-[#25A7CA]/35 hover:bg-white"
                                        >
                                            <div className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm">
                                                <Icon icon={item.icon} width={18} />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600">
                                                        {item.type}
                                                    </span>
                                                    <span className="text-[11px] text-slate-400">{item.time}</span>
                                                </div>
                                                <p className="mt-2 text-sm font-semibold text-slate-900">{item.title}</p>
                                                <p className="mt-1 text-xs text-slate-500">{item.meta}</p>
                                            </div>
                                        </button>
                                    ))
                                ) : (
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                        Todavía no hay actividad reciente para mostrar.
                                    </div>
                                )}
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
