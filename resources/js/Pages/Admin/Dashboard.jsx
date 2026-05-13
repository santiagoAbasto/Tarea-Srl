import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";
import { Head, router, usePage } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from "recharts";

function formatCompact(value) {
    return new Intl.NumberFormat("es-AR", {
        notation: "compact",
        maximumFractionDigits: value > 999 ? 1 : 0,
    }).format(Number(value || 0));
}

function formatMetricValue(value, unit = "") {
    if (unit === "ms") {
        return `${Math.round(Number(value || 0))} ms`;
    }

    if (unit === "MB") {
        return `${Number(value || 0).toFixed(1)} MB`;
    }

    return `${value ?? 0}${unit ? ` ${unit}` : ""}`;
}

function toneClasses(tone) {
    const tones = {
        cyan: {
            badge: "bg-cyan-500/12 text-cyan-700 border-cyan-200/70",
            glow: "from-cyan-500/20 via-cyan-200/20 to-white",
            icon: "bg-cyan-500/12 text-cyan-700",
        },
        teal: {
            badge: "bg-teal-500/12 text-teal-700 border-teal-200/70",
            glow: "from-teal-500/18 via-teal-200/22 to-white",
            icon: "bg-teal-500/12 text-teal-700",
        },
        amber: {
            badge: "bg-amber-500/12 text-amber-700 border-amber-200/70",
            glow: "from-amber-500/18 via-amber-200/22 to-white",
            icon: "bg-amber-500/12 text-amber-700",
        },
        rose: {
            badge: "bg-rose-500/12 text-rose-700 border-rose-200/70",
            glow: "from-rose-500/18 via-rose-200/22 to-white",
            icon: "bg-rose-500/12 text-rose-700",
        },
        emerald: {
            badge: "bg-emerald-500/12 text-emerald-700 border-emerald-200/70",
            glow: "from-emerald-500/18 via-emerald-200/22 to-white",
            icon: "bg-emerald-500/12 text-emerald-700",
        },
        slate: {
            badge: "bg-slate-500/12 text-slate-700 border-slate-200/70",
            glow: "from-slate-500/12 via-slate-200/18 to-white",
            icon: "bg-slate-500/12 text-slate-700",
        },
    };

    return tones[tone] ?? tones.cyan;
}

function SectionCard({ title, description, icon, children, aside = null }) {
    return (
        <section className="rounded-[30px] border border-slate-200/90 bg-white/90 p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur">
            <div className="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                        <Icon icon={icon} width={20} />
                    </div>
                    <div>
                        <h2 className="text-xl font-semibold tracking-tight text-slate-900">
                            {title}
                        </h2>
                        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            {description}
                        </p>
                    </div>
                </div>
                {aside}
            </div>
            <div className="mt-6">{children}</div>
        </section>
    );
}

function PremiumMetricCard({ label, value, helper, icon, tone = "cyan" }) {
    const theme = toneClasses(tone);

    return (
        <motion.article
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
            className={`relative overflow-hidden rounded-[28px] border border-white/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(248,250,252,0.98))] p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]`}
        >
            <div className={`absolute inset-0 bg-gradient-to-br ${theme.glow}`} />
            <div className="relative">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                            {label}
                        </p>
                        <p className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            {value}
                        </p>
                    </div>
                    <div className={`flex h-12 w-12 items-center justify-center rounded-2xl ${theme.icon}`}>
                        <Icon icon={icon} width={20} />
                    </div>
                </div>
                <p className="mt-4 text-sm leading-6 text-slate-500">{helper}</p>
            </div>
        </motion.article>
    );
}

function SystemMetricCard({ item }) {
    const tone = toneClasses(
        item.tone === "emerald"
            ? "emerald"
            : item.tone === "amber"
              ? "amber"
              : item.tone === "rose"
                ? "rose"
                : "cyan",
    );
    const progress = item.goal ? Math.min((Number(item.value || 0) / item.goal) * 100, 100) : 0;

    return (
        <div className="rounded-[24px] border border-slate-200 bg-slate-50/90 p-4">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                        {item.label}
                    </p>
                    <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">
                        {formatMetricValue(item.value, item.unit)}
                    </p>
                </div>
                <div className={`rounded-2xl border px-3 py-2 text-xs font-semibold ${tone.badge}`}>
                    Meta {formatMetricValue(item.goal, item.unit)}
                </div>
            </div>
            <div className="mt-4 h-2.5 overflow-hidden rounded-full bg-white">
                <div
                    className={`h-full rounded-full ${item.tone === "emerald" ? "bg-emerald-500" : item.tone === "amber" ? "bg-amber-500" : item.tone === "rose" ? "bg-rose-500" : "bg-cyan-500"}`}
                    style={{ width: `${progress}%` }}
                />
            </div>
        </div>
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

function SimpleTooltip({ active, payload, label }) {
    if (!active || !payload?.length) {
        return null;
    }

    return (
        <div className="rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 shadow-xl backdrop-blur">
            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                {label}
            </p>
            <div className="mt-2 space-y-1.5">
                {payload.map((entry) => (
                    <div key={entry.dataKey} className="flex items-center justify-between gap-4 text-sm">
                        <span className="flex items-center gap-2 text-slate-600">
                            <span
                                className="h-2.5 w-2.5 rounded-full"
                                style={{ backgroundColor: entry.color }}
                            />
                            {entry.name}
                        </span>
                        <span className="font-semibold text-slate-900">
                            {entry.value}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function RecentErrorCard({ item }) {
    return (
        <div className="rounded-[22px] border border-rose-100 bg-rose-50/60 p-4">
            <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-rose-700">
                    {item.level}
                </span>
                <span className="text-xs text-slate-500">{item.time}</span>
            </div>
            <p className="mt-3 text-sm font-semibold text-slate-900">{item.message}</p>
            {item.path ? <p className="mt-2 text-xs text-slate-500">{item.path}</p> : null}
            {item.user_agent ? (
                <p className="mt-2 text-xs text-slate-400">{item.user_agent}</p>
            ) : null}
        </div>
    );
}

function SecurityAgentRow({ item, icon }) {
    return (
        <div className="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <div className="flex min-w-0 items-center gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm">
                    <Icon icon={icon} width={17} />
                </div>
                <p className="truncate text-sm font-medium text-slate-700">
                    {item.agent || item.path}
                </p>
            </div>
            <span className="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                {item.count}
            </span>
        </div>
    );
}

function heatTone(count) {
    if (!count) return "bg-slate-200";
    if (count >= 8) return "bg-[#0E7490]";
    if (count >= 5) return "bg-[#25A7CA]";
    if (count >= 3) return "bg-[#7DD7EA]";
    return "bg-[#B7ECF8]";
}

function ActivityHeatmap({ items }) {
    return (
        <div className="overflow-x-auto">
            <div className="min-w-[720px]">
                <div
                    className="grid gap-2"
                    style={{ gridTemplateColumns: "repeat(14, minmax(0, 1fr))" }}
                >
                    {items.map((item) => (
                        <motion.div
                            key={item.date}
                            initial={{ opacity: 0, scale: 0.92 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.18 }}
                            title={`${item.date}: ${item.count} eventos`}
                            className="space-y-1"
                        >
                            <div
                                className={`h-10 rounded-2xl border border-white/70 shadow-sm ${heatTone(item.count)}`}
                            />
                            <p className="text-center text-[10px] font-medium text-slate-400">
                                {item.date.slice(8, 10)}
                            </p>
                        </motion.div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function QuickLinkGroup({ title, items }) {
    return (
        <div className="space-y-3">
            <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                {title}
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
    );
}

export default function Dashboard({
    stats,
    quickLinks = [],
    inbox,
    seo,
    analytics,
    pendingItems = [],
    recentItems = [],
}) {
    const page = usePage();
    const { app } = page.props;

    const groupedQuickLinks = quickLinks.reduce((acc, item) => {
        const group = item.group || "General";
        acc[group] = acc[group] || [];
        acc[group].push(item);
        return acc;
    }, {});

    const leadSeries = analytics?.leadSeries ?? [];
    const securitySeries = analytics?.securitySeries ?? [];
    const errorSeries = analytics?.errors?.series ?? [];
    const channelMix = analytics?.channelMix ?? [];
    const heatmap = analytics?.heatmap ?? [];
    const systemBenchmarks = analytics?.systemBenchmarks ?? [];
    const totalInteractions = leadSeries.reduce((sum, item) => sum + Number(item.total || 0), 0);
    const panelResponse = systemBenchmarks[0]?.value ?? 0;
    const contactsUnread = inbox?.contacts_unread ?? 0;
    const quotesUnread = inbox?.quotes_unread ?? 0;
    const catalogUnread = inbox?.catalog_unread ?? 0;

    return (
        <AdminLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[34px] border border-slate-200/90 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
                    <div className="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.28),_transparent_30%),radial-gradient(circle_at_80%_10%,_rgba(15,23,42,0.18),_transparent_25%),linear-gradient(135deg,#f7fbfd_0%,#ffffff_38%,#edf7fb_100%)] px-6 py-8 md:px-8">
                        <div className="absolute -right-10 top-8 h-40 w-40 rounded-full bg-cyan-200/25 blur-3xl" />
                        <div className="absolute bottom-0 left-1/3 h-32 w-32 rounded-full bg-teal-200/25 blur-3xl" />

                        <div className="relative grid gap-8 xl:grid-cols-[minmax(0,1fr)_390px]">
                            <div className="max-w-3xl">
                                <div className="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/85 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#117a98] shadow-sm backdrop-blur">
                                    <Icon icon="solar:widget-2-outline" width={14} />
                                    Centro de control
                                </div>

                                <h1 className="mt-4 text-3xl font-semibold tracking-tight text-slate-950 md:text-5xl">
                                    Dashboard ejecutivo de {app?.name ?? "Tarea SRL"}
                                </h1>
                                <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-600 md:text-base">
                                    Actividad comercial, bloqueos sospechosos, errores recientes, salud del panel y accesos críticos en una sola vista, con datos reales del sistema.
                                </p>

                                <div className="mt-6 flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onClick={() => router.visit("/admin/contact?tab=requests")}
                                        className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                                    >
                                        <Icon icon="solar:inbox-outline" width={16} />
                                        Revisar entradas
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => router.visit("/admin/seo")}
                                        className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA]/35 hover:text-slate-900"
                                    >
                                        <Icon icon="solar:chart-2-outline" width={16} />
                                        Ver SEO
                                    </button>
                                </div>

                                <div className="mt-8 grid gap-3 sm:grid-cols-3">
                                    <div className="rounded-[22px] border border-white/80 bg-white/80 px-4 py-4 shadow-sm backdrop-blur">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                            Interacciones 14 días
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-950">
                                            {formatCompact(totalInteractions)}
                                        </p>
                                    </div>
                                    <div className="rounded-[22px] border border-white/80 bg-white/80 px-4 py-4 shadow-sm backdrop-blur">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                            Bots y bloqueos
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-950">
                                            {formatCompact(analytics?.security?.blocked_7d ?? 0)}
                                        </p>
                                    </div>
                                    <div className="rounded-[22px] border border-white/80 bg-white/80 px-4 py-4 shadow-sm backdrop-blur">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                            Velocidad panel
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-950">
                                            {formatMetricValue(panelResponse, "ms")}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <motion.div
                                initial={{ opacity: 0, scale: 0.96, y: 16 }}
                                animate={{ opacity: 1, scale: 1, y: 0 }}
                                transition={{ duration: 0.45 }}
                                className="rounded-[30px] border border-white/80 bg-white/82 p-5 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            Estado general
                                        </p>
                                        <p className="mt-2 text-3xl font-semibold text-slate-950">
                                            {seo?.completion ?? 0}%
                                        </p>
                                    </div>
                                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <Icon icon="solar:shield-check-outline" width={20} />
                                    </div>
                                </div>

                                <p className="mt-3 text-sm leading-6 text-slate-500">
                                    {seo?.configured ?? 0} de {seo?.total ?? 0} secciones con metadatos completos y un total de{" "}
                                    {formatCompact((stats?.contacts ?? 0) + (stats?.quotes ?? 0) + (stats?.catalog_requests ?? 0))} solicitudes históricas.
                                </p>

                                <div className="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500"
                                        style={{ width: `${seo?.completion ?? 0}%` }}
                                    />
                                </div>

                                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                                    <div className="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            Bandeja sin leer
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-950">
                                            {contactsUnread + quotesUnread + catalogUnread}
                                        </p>
                                        <p className="mt-2 text-xs text-slate-500">
                                            Contacto, presupuesto y catálogo
                                        </p>
                                    </div>
                                    <div className="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            Eventos escaneados
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-slate-950">
                                            {formatCompact(analytics?.systemSummary?.events_scanned ?? 0)}
                                        </p>
                                        <p className="mt-2 text-xs text-slate-500">
                                            Logs recientes del sistema
                                        </p>
                                    </div>
                                </div>
                            </motion.div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <PremiumMetricCard
                        label="Base de productos"
                        value={formatCompact(stats?.products ?? 0)}
                        helper={`${stats?.families ?? 0} familias activas para el catálogo público.`}
                        icon="solar:box-outline"
                        tone="cyan"
                    />
                    <PremiumMetricCard
                        label="Actividad comercial"
                        value={formatCompact((stats?.contacts ?? 0) + (stats?.quotes ?? 0) + (stats?.catalog_requests ?? 0))}
                        helper="Solicitudes reales recibidas desde la web."
                        icon="solar:inbox-outline"
                        tone="amber"
                    />
                    <PremiumMetricCard
                        label="Riesgo automatizado"
                        value={formatCompact(analytics?.security?.suspected_bots_7d ?? 0)}
                        helper={`${analytics?.security?.blocked_24h ?? 0} bloqueos en las últimas 24 horas.`}
                        icon="solar:shield-warning-outline"
                        tone="rose"
                    />
                    <PremiumMetricCard
                        label="Errores detectados"
                        value={formatCompact(analytics?.errors?.error_7d ?? 0)}
                        helper={`${analytics?.errors?.error_24h ?? 0} errores en las últimas 24 horas.`}
                        icon="solar:danger-triangle-outline"
                        tone="teal"
                    />
                </section>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.28fr)_420px]">
                    <SectionCard
                        title="Actividad del sitio"
                        description="Entradas reales captadas por formularios y suscripciones durante los últimos catorce días."
                        icon="solar:chart-2-outline"
                        aside={
                            <div className="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Dinámico
                            </div>
                        }
                    >
                        <div className="h-[320px]">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={leadSeries}>
                                    <defs>
                                        <linearGradient id="contactsFill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="5%" stopColor="#25A7CA" stopOpacity={0.35} />
                                            <stop offset="95%" stopColor="#25A7CA" stopOpacity={0} />
                                        </linearGradient>
                                        <linearGradient id="quotesFill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="5%" stopColor="#0F766E" stopOpacity={0.28} />
                                            <stop offset="95%" stopColor="#0F766E" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid stroke="#E2E8F0" strokeDasharray="4 4" vertical={false} />
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} />
                                    <YAxis tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} allowDecimals={false} />
                                    <Tooltip content={<SimpleTooltip />} />
                                    <Legend verticalAlign="top" iconType="circle" wrapperStyle={{ paddingBottom: 12 }} />
                                    <Area type="monotone" dataKey="contactos" name="Contacto" stroke="#25A7CA" fill="url(#contactsFill)" strokeWidth={2.5} />
                                    <Area type="monotone" dataKey="presupuestos" name="Presupuesto" stroke="#0F766E" fill="url(#quotesFill)" strokeWidth={2.5} />
                                    <Area type="monotone" dataKey="catalogos" name="Catálogo" stroke="#F59E0B" fillOpacity={0} strokeWidth={2} />
                                    <Area type="monotone" dataKey="suscripciones" name="Newsletter" stroke="#7C3AED" fillOpacity={0} strokeWidth={2} />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </SectionCard>

                    <SectionCard
                        title="Canales de entrada"
                        description="Distribución actual de solicitudes y oportunidades captadas por la web."
                        icon="solar:pie-chart-2-outline"
                    >
                        <div className="space-y-5">
                            <div className="h-[250px]">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={channelMix}
                                            dataKey="value"
                                            nameKey="name"
                                            innerRadius={64}
                                            outerRadius={92}
                                            paddingAngle={4}
                                        >
                                            {channelMix.map((item) => (
                                                <Cell key={item.name} fill={item.color} />
                                            ))}
                                        </Pie>
                                        <Tooltip content={<SimpleTooltip />} />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>

                            <div className="space-y-3">
                                {channelMix.map((item) => (
                                    <div key={item.name} className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div className="flex items-center gap-3">
                                            <span className="h-3 w-3 rounded-full" style={{ backgroundColor: item.color }} />
                                            <div>
                                                <p className="text-sm font-semibold text-slate-900">{item.name}</p>
                                                <p className="text-xs text-slate-500">
                                                    {item.unread > 0 ? `${item.unread} sin leer` : "Sin pendientes"}
                                                </p>
                                            </div>
                                        </div>
                                        <span className="text-lg font-semibold text-slate-950">
                                            {item.value}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </SectionCard>
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.08fr)_minmax(0,0.92fr)]">
                    <SectionCard
                        title="Mapa de calor"
                        description="Pulso diario del sitio combinando solicitudes, suscripciones y eventos recientes del sistema."
                        icon="solar:fire-outline"
                        aside={
                            <div className="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Últimos 84 días
                            </div>
                        }
                    >
                        <ActivityHeatmap items={heatmap} />
                        <div className="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span>Menor actividad</span>
                            <span className="h-3 w-3 rounded bg-[#E2E8F0]" />
                            <span className="h-3 w-3 rounded bg-[#B7ECF8]" />
                            <span className="h-3 w-3 rounded bg-[#7DD7EA]" />
                            <span className="h-3 w-3 rounded bg-[#25A7CA]" />
                            <span className="h-3 w-3 rounded bg-[#0E7490]" />
                            <span>Mayor actividad</span>
                        </div>
                    </SectionCard>

                    <SectionCard
                        title="Tiempos de respuesta"
                        description="Salud del panel medida en vivo al momento de cargar este dashboard."
                        icon="solar:stopwatch-outline"
                    >
                        <div className="space-y-4">
                            {systemBenchmarks.map((item) => (
                                <SystemMetricCard key={item.label} item={item} />
                            ))}
                        </div>

                        <div className="mt-6 grid gap-3 sm:grid-cols-3">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Archivo de logs
                                </p>
                                <p className="mt-2 text-xl font-semibold text-slate-950">
                                    {(analytics?.systemSummary?.log_size_mb ?? 0).toFixed(2)} MB
                                </p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Eventos analizados
                                </p>
                                <p className="mt-2 text-xl font-semibold text-slate-950">
                                    {analytics?.systemSummary?.events_scanned ?? 0}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Último evento
                                </p>
                                <p className="mt-2 text-sm font-semibold text-slate-950">
                                    {analytics?.systemSummary?.last_log_event ?? "Sin datos"}
                                </p>
                            </div>
                        </div>
                    </SectionCard>
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                    <SectionCard
                        title="Detección de bots y bloqueos"
                        description="Eventos sospechosos, intentos frenados y puntos de entrada que conviene vigilar."
                        icon="solar:shield-warning-outline"
                    >
                        <div className="grid gap-4 md:grid-cols-4">
                            <div className="rounded-[24px] border border-amber-200 bg-amber-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">
                                    Bloqueos 24h
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.security?.blocked_24h ?? 0}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-rose-200 bg-rose-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-700">
                                    Bloqueos 7d
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.security?.blocked_7d ?? 0}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-cyan-200 bg-cyan-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-700">
                                    Límites activados
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.security?.rate_limits_7d ?? 0}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">
                                    Accesos negados
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.security?.denied_admin_7d ?? 0}
                                </p>
                            </div>
                        </div>

                        <div className="mt-6 h-[250px]">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={securitySeries}>
                                    <CartesianGrid stroke="#E2E8F0" strokeDasharray="4 4" vertical={false} />
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} />
                                    <YAxis tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} allowDecimals={false} />
                                    <Tooltip content={<SimpleTooltip />} />
                                    <Legend verticalAlign="top" iconType="circle" wrapperStyle={{ paddingBottom: 12 }} />
                                    <Bar dataKey="bloqueos" name="Bloqueos" fill="#F59E0B" radius={[8, 8, 0, 0]} />
                                    <Bar dataKey="accesos" name="Accesos negados" fill="#E11D48" radius={[8, 8, 0, 0]} />
                                    <Bar dataKey="limites" name="Límites" fill="#25A7CA" radius={[8, 8, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>

                        <div className="mt-6 grid gap-4 xl:grid-cols-2">
                            <div className="space-y-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                    Agentes sospechosos
                                </p>
                                {analytics?.security?.top_agents?.length ? (
                                    analytics.security.top_agents.map((item) => (
                                        <SecurityAgentRow key={`${item.agent}-${item.count}`} item={item} icon="solar:ghost-outline" />
                                    ))
                                ) : (
                                    <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                                        No se detectaron agentes sospechosos en este recorte.
                                    </div>
                                )}
                            </div>
                            <div className="space-y-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                    Rutas más atacadas
                                </p>
                                {analytics?.security?.top_paths?.length ? (
                                    analytics.security.top_paths.map((item) => (
                                        <SecurityAgentRow key={`${item.path}-${item.count}`} item={item} icon="solar:route-outline" />
                                    ))
                                ) : (
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                        No hay rutas conflictivas para mostrar.
                                    </div>
                                )}
                            </div>
                        </div>
                    </SectionCard>

                    <SectionCard
                        title="Errores de la web"
                        description="Resumen real de incidentes registrados y últimos errores encontrados en los logs."
                        icon="solar:danger-triangle-outline"
                    >
                        <div className="grid gap-4 sm:grid-cols-3">
                            <div className="rounded-[24px] border border-rose-200 bg-rose-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-700">
                                    Errores 24h
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.errors?.error_24h ?? 0}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-rose-200 bg-rose-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-700">
                                    Errores 7d
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.errors?.error_7d ?? 0}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-amber-200 bg-amber-50 px-4 py-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">
                                    Alertas 7d
                                </p>
                                <p className="mt-2 text-2xl font-semibold text-slate-950">
                                    {analytics?.errors?.warning_7d ?? 0}
                                </p>
                            </div>
                        </div>

                        <div className="mt-6 h-[220px]">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={errorSeries}>
                                    <CartesianGrid stroke="#E2E8F0" strokeDasharray="4 4" vertical={false} />
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} />
                                    <YAxis tickLine={false} axisLine={false} tick={{ fill: "#64748B", fontSize: 12 }} allowDecimals={false} />
                                    <Tooltip content={<SimpleTooltip />} />
                                    <Legend verticalAlign="top" iconType="circle" wrapperStyle={{ paddingBottom: 12 }} />
                                    <Bar dataKey="errores" name="Errores" fill="#E11D48" radius={[8, 8, 0, 0]} />
                                    <Bar dataKey="alertas" name="Alertas" fill="#F59E0B" radius={[8, 8, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>

                        <div className="mt-6 space-y-3">
                            {analytics?.errors?.recent?.length ? (
                                analytics.errors.recent.map((item, index) => (
                                    <RecentErrorCard key={`${item.time}-${index}`} item={item} />
                                ))
                            ) : (
                                <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                                    No hay errores recientes en este recorte del sistema.
                                </div>
                            )}
                        </div>
                    </SectionCard>
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <SectionCard
                        title="Accesos por área"
                        description="Entradas directas a los módulos que más se usan, agrupadas por tipo de tarea."
                        icon="solar:widget-4-outline"
                    >
                        <div className="grid gap-6 lg:grid-cols-2">
                            {Object.entries(groupedQuickLinks).map(([group, items]) => (
                                <QuickLinkGroup key={group} title={group} items={items} />
                            ))}
                        </div>
                    </SectionCard>

                    <div className="space-y-6">
                        <SectionCard
                            title="Pendientes"
                            description="Lo que conviene revisar primero para que no se enfríen oportunidades."
                            icon="solar:bell-bing-outline"
                        >
                            <div className="space-y-3">
                                {pendingItems.length ? (
                                    pendingItems.map((item) => <PendingCard key={item.title} item={item} />)
                                ) : (
                                    <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                                        No hay pendientes urgentes. El panel está al día.
                                    </div>
                                )}
                            </div>
                        </SectionCard>

                        <SectionCard
                            title="Actividad reciente"
                            description="Últimos movimientos relevantes detectados dentro del sitio y del panel."
                            icon="solar:history-outline"
                        >
                            <div className="space-y-3">
                                {recentItems.length ? (
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
                        </SectionCard>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
