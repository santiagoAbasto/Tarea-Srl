import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

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

function emptyPolicyForm(policy) {
    const fields = Object.fromEntries(
        (policy?.fields ?? []).map((field) => [field.field_key, field]),
    );

    return {
        id: policy?.id ?? null,
        title: policy?.title ?? "",
        description: policy?.description ?? "",
        media_id: policy?.media_id ?? null,
        media_url: policy?.media_url ?? "",
        image_file: null,
        secondary_media_id: policy?.secondary_media_id ?? null,
        secondary_media_url: policy?.secondary_media_url ?? "",
        video_file: null,
        media_type: fields.media_type?.field_value ?? "image",
        youtube_url: fields.youtube_url?.field_value ?? "",
        media_type_field_id: fields.media_type?.id ?? null,
        youtube_field_id: fields.youtube_url?.id ?? null,
        sort_order: policy?.sort_order ?? "B",
        is_active: policy?.is_active ?? true,
    };
}

function emptyLogosForm(logos) {
    return {
        id: logos?.id ?? null,
        title: logos?.title ?? "",
        sort_order: logos?.sort_order ?? "C",
        is_active: logos?.is_active ?? true,
        items: (logos?.items ?? []).map((item) => ({
            id: item.id ?? null,
            item_key: item.item_key ?? null,
            title: item.title ?? "",
            media_id: item.media_id ?? null,
            media_url: item.media_url ?? "",
            file: null,
            sort_order: item.sort_order ?? "A",
            is_active: item.is_active ?? true,
        })),
    };
}

function createLogoItem(position = 0) {
    return {
        id: null,
        item_key: null,
        title: "",
        media_id: null,
        media_url: "",
        file: null,
        sort_order: String.fromCharCode(65 + position),
        is_active: true,
    };
}

async function uploadAsset(file, title) {
    const payload = new FormData();
    payload.append("file", file);
    payload.append("title", title || file.name);

    const response = await axios.post("/admin/api/media-assets", payload, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
    });

    return response.data;
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

export default function QualityIndex({
    hero,
    policy,
    logos,
    stats,
    publicQualityUrl,
}) {
    const [previewOpen, setPreviewOpen] = useState(false);
    const [heroForm, setHeroForm] = useState(emptyHeroForm(hero));
    const [policyForm, setPolicyForm] = useState(emptyPolicyForm(policy));
    const [logosForm, setLogosForm] = useState(emptyLogosForm(logos));
    const [heroSaving, setHeroSaving] = useState(false);
    const [policySaving, setPolicySaving] = useState(false);
    const [logosSaving, setLogosSaving] = useState(false);

    useEffect(() => {
        setHeroForm(emptyHeroForm(hero));
    }, [hero]);

    useEffect(() => {
        setPolicyForm(emptyPolicyForm(policy));
    }, [policy]);

    useEffect(() => {
        setLogosForm(emptyLogosForm(logos));
    }, [logos]);

    const reloadPage = () => {
        router.reload();
    };

    const saveHero = async () => {
        setHeroSaving(true);

        try {
            let mediaId = heroForm.media_id;

            if (heroForm.image_file) {
                const uploaded = await uploadAsset(
                    heroForm.image_file,
                    heroForm.title || "Calidad banner",
                );
                mediaId = uploaded.id;
            }

            await axios.put(`/admin/api/site-sections/${heroForm.id}`, {
                page_key: "calidad",
                section_key: "quality_banner",
                title: heroForm.title || null,
                media_id: mediaId,
                sort_order: heroForm.sort_order || "A",
                is_active: heroForm.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            });

            emitAdminToast("La portada de calidad se actualizó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la portada de calidad.",
                "error",
            );
        } finally {
            setHeroSaving(false);
        }
    };

    const savePolicy = async () => {
        setPolicySaving(true);

        try {
            let mediaId = policyForm.media_id;
            let secondaryMediaId = policyForm.secondary_media_id;

            if (policyForm.image_file) {
                const uploaded = await uploadAsset(
                    policyForm.image_file,
                    policyForm.title || "Calidad imagen",
                );
                mediaId = uploaded.id;
            }

            if (policyForm.video_file) {
                const uploaded = await uploadAsset(
                    policyForm.video_file,
                    policyForm.title || "Calidad video",
                );
                secondaryMediaId = uploaded.id;
            }

            await axios.put(`/admin/api/site-sections/${policyForm.id}`, {
                page_key: "calidad",
                section_key: "quality_policy",
                title: policyForm.title || null,
                description: policyForm.description || null,
                media_id: mediaId,
                secondary_media_id:
                    policyForm.media_type === "video" ? secondaryMediaId : null,
                sort_order: policyForm.sort_order || "B",
                is_active: policyForm.is_active,
                sync_field_values: true,
                sync_items: false,
                field_values: [
                    {
                        id: policyForm.media_type_field_id,
                        field_key: "media_type",
                        field_label: "media_type",
                        field_type: "text",
                        field_value: policyForm.media_type,
                        sort_order: "A",
                        is_active: true,
                    },
                    {
                        id: policyForm.youtube_field_id,
                        field_key: "youtube_url",
                        field_label: "youtube_url",
                        field_type: "text",
                        field_value:
                            policyForm.media_type === "youtube"
                                ? policyForm.youtube_url || ""
                                : "",
                        sort_order: "B",
                        is_active: true,
                    },
                ],
                items: [],
            });

            emitAdminToast(
                "La sección de políticas de calidad se actualizó correctamente.",
            );
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la sección de calidad.",
                "error",
            );
        } finally {
            setPolicySaving(false);
        }
    };

    const saveLogos = async () => {
        setLogosSaving(true);

        try {
            const items = [];

            for (const [index, item] of logosForm.items.entries()) {
                let mediaId = item.media_id;

                if (!mediaId && item.file) {
                    const uploaded = await uploadAsset(
                        item.file,
                        item.title || `Logo calidad ${index + 1}`,
                    );
                    mediaId = uploaded.id;
                }

                if (!mediaId) {
                    continue;
                }

                items.push({
                    id: item.id,
                    item_key: item.item_key || `logo_${index + 1}`,
                    title: item.title || null,
                    media_id: mediaId,
                    sort_order: String.fromCharCode(65 + index),
                    is_active: item.is_active,
                });
            }

            await axios.put(`/admin/api/site-sections/${logosForm.id}`, {
                page_key: "calidad",
                section_key: "quality_logos",
                title: logosForm.title || null,
                sort_order: logosForm.sort_order || "C",
                is_active: logosForm.is_active,
                sync_field_values: false,
                sync_items: true,
                field_values: [],
                items,
            });

            emitAdminToast("Los logos de calidad se actualizaron correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudieron guardar los logos de calidad.",
                "error",
            );
        } finally {
            setLogosSaving(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Calidad" />

            <PublicPreviewModal
                open={previewOpen}
                title="Vista pública de Calidad"
                url={publicQualityUrl}
                onClose={() => setPreviewOpen(false)}
            />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:shield-check-outline" width={14} />
                                    Calidad / Política y certificaciones
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Gestión de calidad
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Desde acá administrás el banner, el contenido de
                                    políticas de calidad y los logos que se muestran
                                    en la página pública.
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
                    <StatCard
                        label="Logos activos"
                        value={stats?.logos ?? logosForm.items.filter((item) => item.is_active).length}
                        icon="solar:gallery-outline"
                    />
                    <StatCard
                        label="Tipo multimedia"
                        value={
                            policyForm.media_type === "youtube"
                                ? "YouTube"
                                : policyForm.media_type === "video"
                                  ? "Video"
                                  : "Imagen"
                        }
                        icon="solar:videocamera-record-outline"
                    />
                </section>

                <section className="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6">
                            <h2 className="text-2xl font-semibold text-slate-900">
                                Banner principal
                            </h2>
                            <p className="mt-2 text-sm text-slate-500">
                                Controla el hero superior de la página de calidad.
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

                            <Field label="Imagen del banner">
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) =>
                                        setHeroForm((current) => ({
                                            ...current,
                                            image_file:
                                                event.target.files?.[0] ?? null,
                                        }))
                                    }
                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                />
                                {heroForm.media_url ? (
                                    <img
                                        src={heroForm.media_url}
                                        alt={heroForm.title || "Banner calidad"}
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
                                Políticas de calidad
                            </h2>
                            <p className="mt-2 text-sm text-slate-500">
                                Editá el título, el texto y el contenido multimedia
                                que acompaña esta sección pública.
                            </p>
                        </div>

                        <div className="space-y-5">
                            <Field label="Título">
                                <input
                                    type="text"
                                    value={policyForm.title}
                                    onChange={(event) =>
                                        setPolicyForm((current) => ({
                                            ...current,
                                            title: event.target.value,
                                        }))
                                    }
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </Field>

                            <Field label="Contenido">
                                <RichTextEditor
                                    value={policyForm.description}
                                    onChange={(value) =>
                                        setPolicyForm((current) => ({
                                            ...current,
                                            description: value,
                                        }))
                                    }
                                    placeholder="Escribí el contenido de políticas de calidad..."
                                />
                            </Field>

                            <Field label="Tipo multimedia">
                                <select
                                    value={policyForm.media_type}
                                    onChange={(event) =>
                                        setPolicyForm((current) => ({
                                            ...current,
                                            media_type: event.target.value,
                                        }))
                                    }
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                >
                                    <option value="image">Imagen</option>
                                    <option value="video">Video local</option>
                                    <option value="youtube">YouTube</option>
                                </select>
                            </Field>

                            <Field label="Imagen principal" hint="Se usa como imagen fija y también como portada visual de la sección.">
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) =>
                                        setPolicyForm((current) => ({
                                            ...current,
                                            image_file:
                                                event.target.files?.[0] ?? null,
                                        }))
                                    }
                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                />
                                {policyForm.media_url ? (
                                    <img
                                        src={policyForm.media_url}
                                        alt={policyForm.title || "Calidad"}
                                        className="mt-3 h-48 w-full rounded-2xl border border-slate-200 object-cover"
                                    />
                                ) : null}
                            </Field>

                            {policyForm.media_type === "video" ? (
                                <Field label="Video local">
                                    <input
                                        type="file"
                                        accept="video/*"
                                        onChange={(event) =>
                                            setPolicyForm((current) => ({
                                                ...current,
                                                video_file:
                                                    event.target.files?.[0] ?? null,
                                            }))
                                        }
                                        className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                    />
                                    {policyForm.secondary_media_url ? (
                                        <video
                                            controls
                                            className="mt-3 h-48 w-full rounded-2xl border border-slate-200 bg-slate-950 object-cover"
                                        >
                                            <source src={policyForm.secondary_media_url} />
                                        </video>
                                    ) : null}
                                </Field>
                            ) : null}

                            {policyForm.media_type === "youtube" ? (
                                <Field label="URL de YouTube">
                                    <input
                                        type="text"
                                        value={policyForm.youtube_url}
                                        onChange={(event) =>
                                            setPolicyForm((current) => ({
                                                ...current,
                                                youtube_url: event.target.value,
                                            }))
                                        }
                                        placeholder="https://www.youtube.com/watch?v=..."
                                        className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    />
                                </Field>
                            ) : null}

                            <Toggle
                                label="Bloque activo"
                                checked={policyForm.is_active}
                                onChange={(value) =>
                                    setPolicyForm((current) => ({
                                        ...current,
                                        is_active: value,
                                    }))
                                }
                            />

                            <div className="flex justify-end border-t border-slate-200 pt-5">
                                <button
                                    type="button"
                                    onClick={savePolicy}
                                    disabled={policySaving}
                                    className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                >
                                    {policySaving ? "Guardando..." : "Guardar políticas"}
                                </button>
                            </div>
                        </div>
                    </section>
                </section>

                <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 className="text-2xl font-semibold text-slate-900">
                                Logos de calidad
                            </h2>
                            <p className="mt-2 text-sm text-slate-500">
                                Administrá los logos o certificaciones que se ven en
                                la página pública.
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={() =>
                                setLogosForm((current) => ({
                                    ...current,
                                    items: [
                                        ...current.items,
                                        createLogoItem(current.items.length),
                                    ],
                                }))
                            }
                            className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                        >
                            <Icon icon="solar:add-circle-outline" width={18} />
                            Agregar logo
                        </button>
                    </div>

                    <div className="space-y-5">
                        <Field label="Título del bloque">
                            <input
                                type="text"
                                value={logosForm.title}
                                onChange={(event) =>
                                    setLogosForm((current) => ({
                                        ...current,
                                        title: event.target.value,
                                    }))
                                }
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            />
                        </Field>

                        <Toggle
                            label="Bloque activo"
                            checked={logosForm.is_active}
                            onChange={(value) =>
                                setLogosForm((current) => ({
                                    ...current,
                                    is_active: value,
                                }))
                            }
                        />

                        {logosForm.items.length ? (
                            <div className="grid gap-4 xl:grid-cols-2">
                                {logosForm.items.map((item, index) => (
                                    <article
                                        key={item.id ?? `logo-${index}`}
                                        className="rounded-[26px] border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <div className="space-y-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <p className="text-sm font-semibold text-slate-900">
                                                    Logo {index + 1}
                                                </p>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setLogosForm((current) => ({
                                                            ...current,
                                                            items: current.items.filter(
                                                                (_, currentIndex) =>
                                                                    currentIndex !== index,
                                                            ),
                                                        }))
                                                    }
                                                    className="rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>

                                            <Field label="Nombre interno">
                                                <input
                                                    type="text"
                                                    value={item.title}
                                                    onChange={(event) =>
                                                        setLogosForm((current) => ({
                                                            ...current,
                                                            items: current.items.map(
                                                                (currentItem, currentIndex) =>
                                                                    currentIndex === index
                                                                        ? {
                                                                              ...currentItem,
                                                                              title: event.target.value,
                                                                          }
                                                                        : currentItem,
                                                            ),
                                                        }))
                                                    }
                                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                                />
                                            </Field>

                                            <Field label="Imagen del logo">
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(event) =>
                                                        setLogosForm((current) => ({
                                                            ...current,
                                                            items: current.items.map(
                                                                (currentItem, currentIndex) =>
                                                                    currentIndex === index
                                                                        ? {
                                                                              ...currentItem,
                                                                              file:
                                                                                  event.target.files?.[0] ??
                                                                                  null,
                                                                          }
                                                                        : currentItem,
                                                            ),
                                                        }))
                                                    }
                                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                                />
                                                {item.media_url ? (
                                                    <div className="mt-3 flex h-32 items-center justify-center rounded-2xl border border-slate-200 bg-white p-4">
                                                        <img
                                                            src={item.media_url}
                                                            alt={item.title || `Logo ${index + 1}`}
                                                            className="max-h-full max-w-full object-contain"
                                                        />
                                                    </div>
                                                ) : null}
                                            </Field>

                                            <Toggle
                                                label="Logo activo"
                                                checked={item.is_active}
                                                onChange={(value) =>
                                                    setLogosForm((current) => ({
                                                        ...current,
                                                        items: current.items.map(
                                                            (currentItem, currentIndex) =>
                                                                currentIndex === index
                                                                    ? {
                                                                          ...currentItem,
                                                                          is_active: value,
                                                                      }
                                                                    : currentItem,
                                                        ),
                                                    }))
                                                }
                                            />
                                        </div>
                                    </article>
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-[26px] border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                    <Icon icon="solar:gallery-outline" width={24} />
                                </div>
                                <h3 className="mt-4 text-lg font-semibold text-slate-900">
                                    Todavía no hay logos cargados
                                </h3>
                                <p className="mt-2 text-sm text-slate-500">
                                    Agregá los logos de certificación o calidad que
                                    querés mostrar en esta sección.
                                </p>
                            </div>
                        )}

                        <div className="flex justify-end border-t border-slate-200 pt-5">
                            <button
                                type="button"
                                onClick={saveLogos}
                                disabled={logosSaving}
                                className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                            >
                                {logosSaving ? "Guardando..." : "Guardar logos"}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
