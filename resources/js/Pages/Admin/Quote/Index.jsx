import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

const FIELD_DEFINITIONS = [
    { key: "name_label", label: "Etiqueta nombre", defaultValue: "Nombre y Apellido*", sortOrder: "A" },
    { key: "email_label", label: "Etiqueta email", defaultValue: "Email*", sortOrder: "B" },
    { key: "company_label", label: "Etiqueta empresa", defaultValue: "Empresa", sortOrder: "C" },
    { key: "phone_label", label: "Etiqueta teléfono", defaultValue: "Teléfono*", sortOrder: "D" },
    { key: "message_label", label: "Etiqueta mensaje", defaultValue: "Mensaje", sortOrder: "E" },
    { key: "button_text", label: "Texto botón", defaultValue: "Enviar solicitud", sortOrder: "F" },
];

function emptyHeroForm(hero) {
    return {
        id: hero?.id ?? null,
        title: hero?.title ?? "",
        media_id: hero?.media_id ?? null,
        media_url: hero?.media_url ?? "",
        image_file: null,
        sort_order: hero?.sort_order ?? "A",
        is_active: hero?.is_active ?? true,
    };
}

function emptyContentForm(content) {
    const fields = Object.fromEntries(
        FIELD_DEFINITIONS.map((field) => {
            const current = (content?.fields ?? []).find((item) => item.field_key === field.key);

            return [
                field.key,
                {
                    id: current?.id ?? null,
                    field_key: field.key,
                    field_label: current?.field_label ?? field.label,
                    field_type: current?.field_type ?? "text",
                    field_value: current?.field_value ?? field.defaultValue,
                    sort_order: current?.sort_order ?? field.sortOrder,
                    is_active: current?.is_active ?? true,
                },
            ];
        }),
    );

    return {
        id: content?.id ?? null,
        title: content?.title ?? "",
        subtitle: content?.subtitle ?? "",
        sort_order: content?.sort_order ?? "B",
        is_active: content?.is_active ?? true,
        fields,
    };
}

const QUOTE_BANNER_GUIDE = {
    title: "Imagen para la portada de presupuesto",
    detail: "Usá una imagen horizontal para que se vea bien en la parte superior de la página.",
    limit: "Peso máximo: 10 MB.",
    note: "Si respetás esta medida, la portada se va a ver más limpia y ordenada.",
    size: "1366 x 350 px",
};

async function uploadAsset(file, title) {
    const payload = new FormData();
    payload.append("file", file);
    payload.append("title", title || file.name);
    payload.append("type", "image");

    const response = await axios.post("/admin/api/media-assets", payload, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
    });

    return response.data;
}

function TabButton({ active, icon, label, onClick, badge = null }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`inline-flex items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold transition ${
                active
                    ? "border-[#25A7CA] bg-[#25A7CA]/10 text-[#117a98]"
                    : "border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900"
            }`}
        >
            <Icon icon={icon} width={18} />
            <span>{label}</span>
            {badge ? (
                <span className="rounded-full bg-[#25A7CA] px-2 py-0.5 text-[11px] font-semibold text-white">
                    {badge}
                </span>
            ) : null}
        </button>
    );
}

function StatCard({ label, value, icon }) {
    return (
        <article className="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-slate-500">{label}</p>
                    <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                        {value}
                    </p>
                </div>
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#117a98]">
                    <Icon icon={icon} width={22} />
                </div>
            </div>
        </article>
    );
}

function Field({ label, hint = null, children }) {
    return (
        <label className="block space-y-2">
            <div>
                <p className="text-sm font-semibold text-slate-900">{label}</p>
                {hint ? <p className="mt-1 text-xs text-slate-500">{hint}</p> : null}
            </div>
            {children}
        </label>
    );
}

function RecommendationCard({ title, detail, limit, note, size }) {
    return (
        <div className="rounded-[24px] border border-[#25A7CA]/20 bg-[linear-gradient(135deg,rgba(37,167,202,0.18),rgba(255,255,255,0.98))] p-5 shadow-sm">
            <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div className="max-w-2xl space-y-2">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                        Recomendación importante
                    </p>
                    <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
                    <p className="text-sm leading-6 text-slate-600">{detail}</p>
                    <p className="text-sm font-semibold text-slate-900">{limit}</p>
                    <p className="text-xs leading-5 text-slate-500">{note}</p>
                </div>

                <div className="rounded-[22px] border border-white/70 bg-white/80 px-5 py-4 text-center shadow-sm backdrop-blur">
                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Tamaño recomendado
                    </p>
                    <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">
                        {size}
                    </p>
                </div>
            </div>
        </div>
    );
}

function Toggle({ label, checked, onChange }) {
    return (
        <button
            type="button"
            onClick={() => onChange(!checked)}
            className={`inline-flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium transition ${
                checked
                    ? "border-[#25A7CA]/30 bg-[#25A7CA]/10 text-[#117a98]"
                    : "border-slate-200 bg-white text-slate-600 hover:border-slate-300"
            }`}
        >
            <span
                className={`flex h-6 w-10 items-center rounded-full p-1 transition ${
                    checked ? "bg-[#25A7CA]" : "bg-slate-300"
                }`}
            >
                <span
                    className={`h-4 w-4 rounded-full bg-white transition ${
                        checked ? "translate-x-4" : "translate-x-0"
                    }`}
                />
            </span>
            <span>{label}</span>
        </button>
    );
}

export default function QuoteIndex({
    hero,
    content,
    requests: initialRequests,
    stats,
    initialTab,
    publicQuoteUrl,
}) {
    const [tab, setTab] = useState(initialTab || "page");
    const [previewOpen, setPreviewOpen] = useState(false);
    const [heroForm, setHeroForm] = useState(emptyHeroForm(hero));
    const [contentForm, setContentForm] = useState(emptyContentForm(content));
    const [heroSaving, setHeroSaving] = useState(false);
    const [contentSaving, setContentSaving] = useState(false);
    const [requests, setRequests] = useState(initialRequests ?? []);
    const [requestBusyId, setRequestBusyId] = useState(null);

    useEffect(() => {
        setTab(initialTab || "page");
    }, [initialTab]);

    useEffect(() => {
        setHeroForm(emptyHeroForm(hero));
    }, [hero]);

    useEffect(() => {
        setContentForm(emptyContentForm(content));
    }, [content]);

    useEffect(() => {
        setRequests(initialRequests ?? []);
    }, [initialRequests]);

    const visitTab = (nextTab) => {
        setTab(nextTab);
        router.visit(`/admin/quote?tab=${nextTab}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const reloadPage = () => {
        router.reload({
            onSuccess: (page) => {
                setHeroForm(emptyHeroForm(page.props.hero));
                setContentForm(emptyContentForm(page.props.content));
                setRequests(page.props.requests ?? []);
            },
        });
    };

    const saveHero = async () => {
        setHeroSaving(true);

        try {
            let mediaId = heroForm.media_id;

            if (heroForm.image_file) {
                const uploaded = await uploadAsset(
                    heroForm.image_file,
                    heroForm.title || "Presupuesto banner",
                );
                mediaId = uploaded.id;
            }

            await axios.put(`/admin/api/site-sections/${heroForm.id}`, {
                page_key: "presupuesto",
                section_key: "presupuesto_banner",
                title: heroForm.title || null,
                media_id: mediaId,
                sort_order: heroForm.sort_order || "A",
                is_active: heroForm.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            });

            emitAdminToast("La portada de presupuesto se actualizó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la portada de presupuesto.",
                "error",
            );
        } finally {
            setHeroSaving(false);
        }
    };

    const saveContent = async () => {
        setContentSaving(true);

        try {
            await axios.put(`/admin/api/site-sections/${contentForm.id}`, {
                page_key: "presupuesto",
                section_key: "presupuesto_content",
                title: contentForm.title || null,
                subtitle: contentForm.subtitle || null,
                sort_order: contentForm.sort_order || "B",
                is_active: contentForm.is_active,
                sync_field_values: true,
                sync_items: false,
                field_values: FIELD_DEFINITIONS.map((field) => ({
                    ...contentForm.fields[field.key],
                    field_key: field.key,
                    field_label: field.label,
                    field_type: "text",
                    sort_order: contentForm.fields[field.key]?.sort_order ?? field.sortOrder,
                    is_active: true,
                })),
                items: [],
            });

            emitAdminToast("El formulario de presupuesto se actualizó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar el formulario de presupuesto.",
                "error",
            );
        } finally {
            setContentSaving(false);
        }
    };

    const updateRequest = async (item, patch, successMessage) => {
        setRequestBusyId(item.id);

        try {
            await axios.put(`/admin/api/quote-requests/${item.id}`, {
                name: item.name,
                email: item.email,
                company: item.company,
                phone: item.phone,
                message: item.message,
                is_read: item.is_read,
                status: item.status || "pendiente",
                ...patch,
            });

            emitAdminToast(successMessage);
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo actualizar la solicitud.",
                "error",
            );
        } finally {
            setRequestBusyId(null);
        }
    };

    const deleteRequest = async (item) => {
        if (!window.confirm(`¿Eliminar la solicitud de presupuesto de "${item.name}"?`)) {
            return;
        }

        setRequestBusyId(item.id);

        try {
            await axios.delete(`/admin/api/quote-requests/${item.id}`);
            emitAdminToast("La solicitud de presupuesto se eliminó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar la solicitud.",
                "error",
            );
        } finally {
            setRequestBusyId(null);
        }
    };

    return (
        <AdminLayout>
            <Head title="Presupuesto" />

            <PublicPreviewModal
                open={previewOpen}
                title="Vista pública de Presupuesto"
                url={publicQuoteUrl}
                onClose={() => setPreviewOpen(false)}
            />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:document-text-outline" width={14} />
                                    Presupuesto / Formulario y solicitudes
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Gestión de presupuesto
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Desde acá administrás el banner, los textos del
                                    formulario público y las solicitudes que llegan
                                    desde la web.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setPreviewOpen(true)}
                                className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                            >
                                <Icon icon="solar:square-arrow-right-up-outline" width={18} />
                                Ver página pública
                            </button>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2">
                    <StatCard label="Solicitudes totales" value={stats?.requests ?? requests.length} icon="solar:inbox-outline" />
                    <StatCard label="Pendientes de leer" value={stats?.unread ?? requests.filter((item) => !item.is_read).length} icon="solar:letter-unread-outline" />
                </section>

                <section className="flex flex-wrap gap-3">
                    <TabButton
                        active={tab === "page"}
                        icon="solar:document-text-outline"
                        label="Página de presupuesto"
                        onClick={() => visitTab("page")}
                    />
                    <TabButton
                        active={tab === "requests"}
                        icon="solar:inbox-outline"
                        label="Solicitudes"
                        badge={stats?.unread ? String(stats.unread) : null}
                        onClick={() => visitTab("requests")}
                    />
                </section>

                {tab === "page" ? (
                    <div className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="mb-6">
                                <h2 className="text-2xl font-semibold text-slate-900">
                                    Banner principal
                                </h2>
                                <p className="mt-2 text-sm text-slate-500">
                                    Controla el hero superior de la página pública de presupuesto.
                                </p>
                            </div>

                            <div className="space-y-5">
                                <Field label="Título">
                                    <input
                                        type="text"
                                        value={heroForm.title}
                                        onChange={(event) =>
                                            setHeroForm((current) => ({
                                                ...current,
                                                title: event.target.value,
                                            }))
                                        }
                                        className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    />
                                </Field>

                                <RecommendationCard {...QUOTE_BANNER_GUIDE} />

                                <Field label="Imagen del banner">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={(event) =>
                                            setHeroForm((current) => ({
                                                ...current,
                                                image_file: event.target.files?.[0] ?? null,
                                            }))
                                        }
                                        className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                    />
                                    {heroForm.media_url ? (
                                        <img
                                            src={heroForm.media_url}
                                            alt={heroForm.title || "Banner presupuesto"}
                                            className="mt-3 h-56 w-full rounded-2xl border border-slate-200 object-cover"
                                        />
                                    ) : null}
                                </Field>

                                <Toggle
                                    label="Bloque activo"
                                    checked={heroForm.is_active}
                                    onChange={(value) =>
                                        setHeroForm((current) => ({
                                            ...current,
                                            is_active: value,
                                        }))
                                    }
                                />

                                <div className="flex justify-end border-t border-slate-200 pt-5">
                                    <button
                                        type="button"
                                        onClick={saveHero}
                                        disabled={heroSaving}
                                        className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                    >
                                        {heroSaving ? "Guardando..." : "Guardar banner"}
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="mb-6">
                                <h2 className="text-2xl font-semibold text-slate-900">
                                    Formulario público
                                </h2>
                                <p className="mt-2 text-sm text-slate-500">
                                    Editá los títulos y labels visibles del formulario de presupuesto.
                                </p>
                            </div>

                            <div className="space-y-5">
                                <Field label="Título bloque de contacto">
                                    <input
                                        type="text"
                                        value={contentForm.title}
                                        onChange={(event) =>
                                            setContentForm((current) => ({
                                                ...current,
                                                title: event.target.value,
                                            }))
                                        }
                                        className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    />
                                </Field>

                                <Field label="Título bloque de productos">
                                    <input
                                        type="text"
                                        value={contentForm.subtitle}
                                        onChange={(event) =>
                                            setContentForm((current) => ({
                                                ...current,
                                                subtitle: event.target.value,
                                            }))
                                        }
                                        className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    />
                                </Field>

                                {FIELD_DEFINITIONS.map((field) => (
                                    <Field key={field.key} label={field.label}>
                                        <input
                                            type="text"
                                            value={contentForm.fields[field.key]?.field_value ?? ""}
                                            onChange={(event) =>
                                                setContentForm((current) => ({
                                                    ...current,
                                                    fields: {
                                                        ...current.fields,
                                                        [field.key]: {
                                                            ...current.fields[field.key],
                                                            field_value: event.target.value,
                                                        },
                                                    },
                                                }))
                                            }
                                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        />
                                    </Field>
                                ))}

                                <Toggle
                                    label="Bloque activo"
                                    checked={contentForm.is_active}
                                    onChange={(value) =>
                                        setContentForm((current) => ({
                                            ...current,
                                            is_active: value,
                                        }))
                                    }
                                />

                                <div className="flex justify-end border-t border-slate-200 pt-5">
                                    <button
                                        type="button"
                                        onClick={saveContent}
                                        disabled={contentSaving}
                                        className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                    >
                                        {contentSaving ? "Guardando..." : "Guardar formulario"}
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                ) : null}

                {tab === "requests" ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6">
                            <h2 className="text-2xl font-semibold text-slate-900">
                                Solicitudes de presupuesto
                            </h2>
                            <p className="mt-2 text-sm text-slate-500">
                                Revisá, respondé y organizá las consultas que llegan desde la web.
                            </p>
                        </div>

                        {requests.length ? (
                            <div className="space-y-4">
                                {requests.map((item) => (
                                    <article
                                        key={item.id}
                                        className={`rounded-[26px] border p-5 transition ${
                                            item.is_read
                                                ? "border-slate-200 bg-slate-50"
                                                : "border-[#25A7CA]/30 bg-[#25A7CA]/5"
                                        }`}
                                    >
                                        <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div className="space-y-3">
                                                <div className="flex flex-wrap items-center gap-3">
                                                    <h3 className="text-lg font-semibold text-slate-900">
                                                        {item.name}
                                                    </h3>
                                                    <span
                                                        className={`rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] ${
                                                            item.is_read
                                                                ? "bg-slate-200 text-slate-600"
                                                                : "bg-[#25A7CA]/12 text-[#117a98]"
                                                        }`}
                                                    >
                                                        {item.is_read ? "Leída" : "Nueva"}
                                                    </span>
                                                    <span className="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                        {item.status || "pendiente"}
                                                    </span>
                                                </div>

                                                <div className="grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                                                    <p><strong className="text-slate-900">Email:</strong> {item.email}</p>
                                                    <p><strong className="text-slate-900">Empresa:</strong> {item.company || "-"}</p>
                                                    <p><strong className="text-slate-900">Teléfono:</strong> {item.phone || "-"}</p>
                                                    <p><strong className="text-slate-900">Fecha:</strong> {item.created_at || "-"}</p>
                                                </div>

                                                {item.items?.length ? (
                                                    <div className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                                        <p className="mb-2 font-semibold text-slate-900">Productos</p>
                                                        <div className="space-y-1">
                                                            {item.items.map((quoteItem, index) => (
                                                                <p key={`${item.id}-product-${index}`}>
                                                                    {quoteItem.product_name} x {quoteItem.quantity}
                                                                </p>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ) : null}

                                                {item.message ? (
                                                    <div className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                                        {item.message}
                                                    </div>
                                                ) : null}

                                                {item.attachments?.length ? (
                                                    <div className="flex flex-wrap gap-2">
                                                        {item.attachments.map((attachment) => (
                                                            <a
                                                                key={attachment.id}
                                                                href={attachment.url}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                                            >
                                                                {attachment.name}
                                                            </a>
                                                        ))}
                                                    </div>
                                                ) : null}
                                            </div>

                                            <div className="flex flex-wrap gap-3 xl:max-w-[340px] xl:justify-end">
                                                {!item.is_read ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            updateRequest(item, { is_read: true }, "La solicitud se marcó como leída.")
                                                        }
                                                        disabled={requestBusyId === item.id}
                                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98] disabled:opacity-60"
                                                    >
                                                        Marcar leída
                                                    </button>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            updateRequest(item, { is_read: false }, "La solicitud se marcó como no leída.")
                                                        }
                                                        disabled={requestBusyId === item.id}
                                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98] disabled:opacity-60"
                                                    >
                                                        Marcar no leída
                                                    </button>
                                                )}

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        updateRequest(item, { status: "resuelta", is_read: true }, "La solicitud se marcó como resuelta.")
                                                    }
                                                    disabled={requestBusyId === item.id}
                                                    className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98] disabled:opacity-60"
                                                >
                                                    Marcar resuelta
                                                </button>

                                                {item.reply_url ? (
                                                    <a
                                                        href={item.reply_url}
                                                        className="rounded-2xl bg-[#25A7CA] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                                                    >
                                                        Responder
                                                    </a>
                                                ) : null}

                                                <button
                                                    type="button"
                                                    onClick={() => deleteRequest(item)}
                                                    disabled={requestBusyId === item.id}
                                                    className="rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-60"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-[26px] border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                    <Icon icon="solar:inbox-outline" width={24} />
                                </div>
                                <h3 className="mt-4 text-lg font-semibold text-slate-900">
                                    Todavía no hay solicitudes
                                </h3>
                                <p className="mt-2 text-sm text-slate-500">
                                    Cuando alguien envíe el formulario de presupuesto, la solicitud aparecerá acá.
                                </p>
                            </div>
                        )}
                    </section>
                ) : null}
            </div>
        </AdminLayout>
    );
}
