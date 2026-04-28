import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

function looksLikeHtml(value) {
    return /<[^>]+>/.test(String(value || ""));
}

function escapeHtml(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function normalizePreviewHtml(value) {
    const stringValue = String(value || "");

    if (stringValue.trim() === "") {
        return "";
    }

    if (looksLikeHtml(stringValue)) {
        return stringValue;
    }

    return stringValue
        .split(/\n{2,}/)
        .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, "<br>")}</p>`)
        .join("");
}

function getInitialForm(section) {
    return {
        id: section?.id ?? null,
        title: section?.title ?? "",
        description: section?.description ?? "",
        button_text: section?.button_text ?? "",
        button_url: section?.button_url ?? "",
        media_id: section?.media_id ?? null,
        media_url: section?.media_url ?? "",
        image_file: null,
        is_active: !!section?.is_active,
        sort_order: section?.sort_order ?? "C",
    };
}

function HomeAboutPreview({ form, previewUrl }) {
    return (
        <div className="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div className="bg-[#093E66]">
                <div className="grid min-h-[560px] xl:grid-cols-[minmax(0,1fr)_539px] xl:gap-16">
                    <div className="min-h-[320px] xl:min-h-[601px]">
                        {previewUrl ? (
                            <img
                                src={previewUrl}
                                alt={form.title || "Home Nosotros"}
                                className="h-full w-full object-cover"
                            />
                        ) : (
                            <div className="flex h-full min-h-[320px] items-center justify-center bg-[radial-gradient(circle_at_top_left,rgba(143,216,248,.22),transparent_42%),linear-gradient(135deg,#0d4d7c_0%,#093E66_100%)] px-8 text-center text-slate-200">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-100">
                                        Preview
                                    </p>
                                    <p className="mt-4 text-sm leading-6">
                                        Subí la imagen del bloque para ver cómo se
                                        integra en la home.
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="flex flex-col items-start px-8 py-12 text-white md:px-10 xl:px-0 xl:py-[89px]">
                        <h2 className="w-full max-w-[539px] font-['Montserrat'] text-[32px] font-semibold leading-[1.2]">
                            {form.title || "Quienes somos"}
                        </h2>

                        <div className="mt-7 w-full max-w-[539px] space-y-6 font-['Raleway'] text-base leading-[1.5] text-white">
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: normalizePreviewHtml(
                                        form.description,
                                    ),
                                }}
                            />
                        </div>

                        {form.button_text ? (
                            <span className="mt-6 inline-flex h-[41px] min-w-[186px] items-center justify-center border border-white px-5 text-center font-['Montserrat'] text-base font-semibold uppercase tracking-[0.02em] text-white">
                                {form.button_text}
                            </span>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function HomeAboutIndex({ section, publicHomeUrl }) {
    const [form, setForm] = useState(getInitialForm(section));
    const [previewUrl, setPreviewUrl] = useState(section?.media_url ?? "");
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);
    const [previewOpen, setPreviewOpen] = useState(false);

    useEffect(() => {
        if (!form.image_file) {
            setPreviewUrl(form.media_url || "");
            return undefined;
        }

        const nextUrl = URL.createObjectURL(form.image_file);
        setPreviewUrl(nextUrl);

        return () => URL.revokeObjectURL(nextUrl);
    }, [form.image_file, form.media_url]);

    const updateForm = (key, value) => {
        setForm((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const handleSave = async (event) => {
        event.preventDefault();
        setSaving(true);
        setErrors({});

        try {
            let mediaId = form.media_id;
            let nextMediaUrl = form.media_url;

            if (form.image_file) {
                const payload = new FormData();
                payload.append("file", form.image_file);
                payload.append("title", form.title || "Home Nosotros");

                const upload = await axios.post("/admin/api/media-assets", payload, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });

                mediaId = upload.data.id;
                nextMediaUrl = previewUrl;
            }

            const payload = {
                page_key: "home",
                section_key: "about_preview",
                title: form.title || null,
                description: form.description || null,
                media_id: mediaId,
                button_text: form.button_text || null,
                button_url: form.button_url || null,
                sort_order: form.sort_order || "C",
                is_active: form.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            };

            const response = await axios.put(
                `/admin/api/site-sections/${form.id}`,
                payload,
            );

            setForm((current) => ({
                ...current,
                media_id: mediaId,
                media_url: nextMediaUrl,
                image_file: null,
                title: response.data.title ?? current.title,
                description: response.data.description ?? current.description,
                button_text: response.data.button_text ?? current.button_text,
                button_url: response.data.button_url ?? current.button_url,
                is_active: !!response.data.is_active,
                sort_order: response.data.sort_order ?? current.sort_order,
            }));

            emitAdminToast("El bloque Home Nosotros se actualizó correctamente.");
        } catch (error) {
            if (error?.response?.status === 422) {
                setErrors(error.response.data.errors ?? {});
            }

            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar el bloque Home Nosotros.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Home Nosotros" />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.18),_transparent_38%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_45%,#eff6ff_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:users-group-rounded-outline" width={14} />
                                    Home / Nosotros
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Bloque Home Nosotros
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Editá el bloque azul de presentación que aparece
                                    en la home pública, manteniendo el mismo estilo
                                    visual del sitio.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setPreviewOpen(true)}
                                className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                            >
                                <Icon icon="solar:square-arrow-right-up-outline" width={18} />
                                Ver home pública
                            </button>
                        </div>
                    </div>
                </section>

                <section className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_460px]">
                    <HomeAboutPreview form={form} previewUrl={previewUrl} />

                    <form
                        onSubmit={handleSave}
                        className="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div className="border-b border-slate-200 pb-5">
                            <h2 className="text-xl font-semibold text-slate-900">
                                Editar contenido
                            </h2>
                            <p className="mt-2 text-sm text-slate-500">
                                Todo lo que guardes acá impacta directamente en la
                                sección `Home Nosotros`.
                            </p>
                        </div>

                        <div className="mt-6 space-y-5">
                            <div>
                                <label className="mb-2 block text-sm font-semibold text-slate-800">
                                    Título
                                </label>
                                <input
                                    type="text"
                                    value={form.title}
                                    onChange={(event) =>
                                        updateForm("title", event.target.value)
                                    }
                                    className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    placeholder="Quienes somos"
                                />
                                {errors.title ? (
                                    <p className="mt-2 text-sm text-red-600">
                                        {errors.title}
                                    </p>
                                ) : null}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-semibold text-slate-800">
                                    Descripción
                                </label>
                                <RichTextEditor
                                    value={form.description}
                                    onChange={(value) =>
                                        updateForm("description", value)
                                    }
                                    placeholder="Texto del bloque..."
                                />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-semibold text-slate-800">
                                    Imagen
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) =>
                                        updateForm(
                                            "image_file",
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                />
                            </div>

                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Texto del botón
                                    </label>
                                    <input
                                        type="text"
                                        value={form.button_text}
                                        onChange={(event) =>
                                            updateForm(
                                                "button_text",
                                                event.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="Mas info"
                                    />
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        URL del botón
                                    </label>
                                    <input
                                        type="text"
                                        value={form.button_url}
                                        onChange={(event) =>
                                            updateForm(
                                                "button_url",
                                                event.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="/nosotros"
                                    />
                                </div>
                            </div>

                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Orden
                                    </label>
                                    <input
                                        type="text"
                                        value={form.sort_order}
                                        onChange={(event) =>
                                            updateForm(
                                                "sort_order",
                                                event.target.value
                                                    .toUpperCase()
                                                    .slice(0, 16),
                                            )
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="C"
                                    />
                                </div>

                                <div className="flex items-end">
                                    <label className="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                                        <input
                                            type="checkbox"
                                            checked={form.is_active}
                                            onChange={(event) =>
                                                updateForm(
                                                    "is_active",
                                                    event.target.checked,
                                                )
                                            }
                                            className="h-4 w-4 rounded border-slate-300 text-[#25A7CA] focus:ring-[#25A7CA]"
                                        />
                                        Bloque activo en home
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="mt-6">
                            <button
                                type="submit"
                                disabled={saving}
                                className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                <Icon icon="solar:diskette-outline" width={18} />
                                {saving ? "Guardando..." : "Guardar cambios"}
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <PublicPreviewModal
                open={previewOpen}
                title="Home pública"
                url={publicHomeUrl}
                onClose={() => setPreviewOpen(false)}
            />
        </AdminLayout>
    );
}
