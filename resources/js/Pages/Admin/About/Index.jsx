import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

function buildSection(section) {
    return {
        id: section?.id ?? null,
        page_key: section?.page_key ?? "nosotros",
        section_key: section?.section_key ?? "",
        title: section?.title ?? "",
        description: section?.description ?? "",
        media_id: section?.media_id ?? null,
        media_url: section?.media_url ?? "",
        image_file: null,
        is_active: !!section?.is_active,
        sort_order: section?.sort_order ?? "A",
        items: (section?.items ?? []).map((item) => ({
            id: item.id ?? null,
            item_key: item.item_key ?? "",
            title: item.title ?? "",
            description: item.description ?? "",
            media_id: item.media_id ?? null,
            media_url: item.media_url ?? "",
            image_file: null,
            is_active: !!item.is_active,
            sort_order: item.sort_order ?? "A",
        })),
    };
}

function buildForm(sections) {
    return {
        hero: buildSection(sections?.hero),
        intro: buildSection(sections?.intro),
        missionVision: buildSection(sections?.missionVision),
        values: buildSection(sections?.values),
        memberships: buildSection(sections?.memberships),
    };
}

function slugify(value) {
    return String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

function alphaSuffix(index) {
    let current = index;
    let suffix = "";

    while (current >= 0) {
        suffix = String.fromCharCode(65 + (current % 26)) + suffix;
        current = Math.floor(current / 26) - 1;
    }

    return suffix;
}

function sortOrderFromIndex(index) {
    return index === 0 ? "A" : `A${alphaSuffix(index - 1)}`;
}

function Field({ label, children, hint }) {
    return (
        <label className="block space-y-2">
            <span className="block text-sm font-semibold text-slate-800">
                {label}
            </span>
            {children}
            {hint ? <p className="text-xs text-slate-500">{hint}</p> : null}
        </label>
    );
}

function TextInput(props) {
    return (
        <input
            {...props}
            className={`w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10 ${
                props.className ?? ""
            }`}
        />
    );
}

function TextArea(props) {
    return (
        <textarea
            {...props}
            className={`min-h-[160px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10 ${
                props.className ?? ""
            }`}
        />
    );
}

function Toggle({ checked, onChange, label }) {
    return (
        <label className="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
                className="h-4 w-4 rounded border-slate-300 text-[#25A7CA] focus:ring-[#25A7CA]"
            />
            <span>{label}</span>
        </label>
    );
}

function SectionCard({ icon, badge, title, description, children }) {
    return (
        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm md:p-7">
            <div className="border-b border-slate-200 pb-5">
                <div className="inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                    <Icon icon={icon} width={14} />
                    {badge}
                </div>
                <h2 className="mt-4 text-2xl font-semibold tracking-tight text-slate-900">
                    {title}
                </h2>
                <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    {description}
                </p>
            </div>

            <div className="mt-6 space-y-5">{children}</div>
        </section>
    );
}

export default function AboutIndex({ sections, publicAboutUrl }) {
    const [form, setForm] = useState(() => buildForm(sections));
    const [saving, setSaving] = useState(false);
    const [previewOpen, setPreviewOpen] = useState(false);
    const [heroPreview, setHeroPreview] = useState(sections?.hero?.media_url ?? "");
    const [introPreview, setIntroPreview] = useState(sections?.intro?.media_url ?? "");

    useEffect(() => {
        setForm(buildForm(sections));
        setHeroPreview(sections?.hero?.media_url ?? "");
        setIntroPreview(sections?.intro?.media_url ?? "");
    }, [sections]);

    useEffect(() => {
        if (!form.hero.image_file) {
            setHeroPreview(form.hero.media_url || "");
            return undefined;
        }

        const nextUrl = URL.createObjectURL(form.hero.image_file);
        setHeroPreview(nextUrl);

        return () => URL.revokeObjectURL(nextUrl);
    }, [form.hero.image_file, form.hero.media_url]);

    useEffect(() => {
        if (!form.intro.image_file) {
            setIntroPreview(form.intro.media_url || "");
            return undefined;
        }

        const nextUrl = URL.createObjectURL(form.intro.image_file);
        setIntroPreview(nextUrl);

        return () => URL.revokeObjectURL(nextUrl);
    }, [form.intro.image_file, form.intro.media_url]);

    const updateSection = (sectionKey, field, value) => {
        setForm((current) => ({
            ...current,
            [sectionKey]: {
                ...current[sectionKey],
                [field]: value,
            },
        }));
    };

    const updateItem = (sectionKey, index, field, value) => {
        setForm((current) => ({
            ...current,
            [sectionKey]: {
                ...current[sectionKey],
                items: current[sectionKey].items.map((item, itemIndex) =>
                    itemIndex === index ? { ...item, [field]: value } : item,
                ),
            },
        }));
    };

    const addMembership = () => {
        setForm((current) => ({
            ...current,
            memberships: {
                ...current.memberships,
                items: [
                    ...current.memberships.items,
                    {
                        id: null,
                        item_key: "",
                        title: "",
                        description: "",
                        media_id: null,
                        media_url: "",
                        image_file: null,
                        is_active: true,
                        sort_order: sortOrderFromIndex(
                            current.memberships.items.length,
                        ),
                    },
                ],
            },
        }));
    };

    const removeMembership = (index) => {
        setForm((current) => ({
            ...current,
            memberships: {
                ...current.memberships,
                items: current.memberships.items.filter(
                    (_, itemIndex) => itemIndex !== index,
                ),
            },
        }));
    };

    const uploadMedia = async (file, title) => {
        const payload = new FormData();
        payload.append("file", file);
        payload.append("title", title || "Nosotros");

        const response = await axios.post("/admin/api/media-assets", payload, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        return response.data.id;
    };

    const saveSection = (sectionId, payload) =>
        axios.put(`/admin/api/site-sections/${sectionId}`, payload);

    const handleSave = async (event) => {
        event.preventDefault();
        setSaving(true);

        try {
            let heroMediaId = form.hero.media_id;
            let introMediaId = form.intro.media_id;

            if (form.hero.image_file) {
                heroMediaId = await uploadMedia(
                    form.hero.image_file,
                    form.hero.title || "Banner Nosotros",
                );
            }

            if (form.intro.image_file) {
                introMediaId = await uploadMedia(
                    form.intro.image_file,
                    form.intro.title || "Intro Nosotros",
                );
            }

            await saveSection(form.hero.id, {
                page_key: "nosotros",
                section_key: "hero",
                title: form.hero.title || null,
                media_id: heroMediaId,
                sort_order: form.hero.sort_order,
                is_active: form.hero.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            });

            await saveSection(form.intro.id, {
                page_key: "nosotros",
                section_key: "intro",
                title: form.intro.title || null,
                description: form.intro.description || null,
                media_id: introMediaId,
                sort_order: form.intro.sort_order,
                is_active: form.intro.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            });

            await saveSection(form.missionVision.id, {
                page_key: "nosotros",
                section_key: "mission_vision",
                title: null,
                description: null,
                media_id: null,
                sort_order: form.missionVision.sort_order,
                is_active: form.missionVision.is_active,
                sync_field_values: false,
                sync_items: true,
                field_values: [],
                items: form.missionVision.items.map((item, index) => ({
                    id: item.id,
                    item_key:
                        item.item_key || (index === 0 ? "mission" : "vision"),
                    title: item.title || null,
                    description: item.description || null,
                    sort_order: sortOrderFromIndex(index),
                    is_active: item.is_active,
                })),
            });

            await saveSection(form.values.id, {
                page_key: "nosotros",
                section_key: "values",
                title: form.values.title || null,
                description: null,
                media_id: null,
                sort_order: form.values.sort_order,
                is_active: form.values.is_active,
                sync_field_values: false,
                sync_items: true,
                field_values: [],
                items: form.values.items.map((item, index) => ({
                    id: item.id,
                    item_key: item.item_key,
                    title: item.title || null,
                    description: null,
                    sort_order: sortOrderFromIndex(index),
                    is_active: item.is_active,
                })),
            });

            const membershipItems = [];

            for (const [index, item] of form.memberships.items.entries()) {
                if (!item.title?.trim() && !item.media_id && !item.image_file) {
                    continue;
                }

                let mediaId = item.media_id;

                if (item.image_file) {
                    mediaId = await uploadMedia(
                        item.image_file,
                        item.title || "Pertenecemos a",
                    );
                }

                membershipItems.push({
                    id: item.id,
                    item_key:
                        item.item_key ||
                        slugify(item.title) ||
                        `membership-${index + 1}`,
                    title: item.title || null,
                    description: null,
                    media_id: mediaId,
                    sort_order: sortOrderFromIndex(index),
                    is_active: item.is_active,
                });
            }

            await saveSection(form.memberships.id, {
                page_key: "nosotros",
                section_key: "memberships",
                title: form.memberships.title || null,
                description: null,
                media_id: null,
                sort_order: form.memberships.sort_order,
                is_active: form.memberships.is_active,
                sync_field_values: false,
                sync_items: true,
                field_values: [],
                items: membershipItems,
            });

            emitAdminToast("La página Nosotros se actualizó correctamente.");
            router.reload();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la página Nosotros.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Nosotros" />

            <PublicPreviewModal
                open={previewOpen}
                title="Vista pública de Nosotros"
                url={publicAboutUrl}
                onClose={() => setPreviewOpen(false)}
            />

            <form onSubmit={handleSave} className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:users-group-rounded-outline" width={14} />
                                    Nosotros / Página pública
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Editar Nosotros
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Desde acá podés editar completo el banner, la
                                    presentación, misión, visión, valores y
                                    pertenencias institucionales de la página
                                    pública de Nosotros.
                                </p>
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row">
                                <button
                                    type="button"
                                    onClick={() => setPreviewOpen(true)}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                >
                                    <Icon icon="solar:square-arrow-right-up-outline" width={18} />
                                    Ver página pública
                                </button>

                                <button
                                    type="submit"
                                    disabled={saving}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                >
                                    <Icon icon="solar:diskette-outline" width={18} />
                                    {saving
                                        ? "Guardando cambios..."
                                        : "Guardar página"}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div className="space-y-6">
                        <SectionCard
                            icon="solar:gallery-wide-outline"
                            badge="Banner"
                            title="Banner principal"
                            description="Controlá la imagen superior y el título principal que encabeza la página."
                        >
                            <Toggle
                                checked={form.hero.is_active}
                                onChange={(value) =>
                                    updateSection("hero", "is_active", value)
                                }
                                label="Mostrar banner"
                            />

                            <Field label="Título del banner">
                                <TextInput
                                    value={form.hero.title}
                                    onChange={(event) =>
                                        updateSection(
                                            "hero",
                                            "title",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="NOSOTROS"
                                />
                            </Field>

                            <Field label="Imagen del banner">
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) =>
                                        updateSection(
                                            "hero",
                                            "image_file",
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                />
                            </Field>

                            <div className="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-50">
                                {heroPreview ? (
                                    <img
                                        src={heroPreview}
                                        alt={form.hero.title || "Banner"}
                                        className="h-[260px] w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex h-[260px] items-center justify-center px-6 text-center text-sm text-slate-500">
                                        Subí una imagen para el banner principal.
                                    </div>
                                )}
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon="solar:document-text-outline"
                            badge="Contenido"
                            title="Contenido principal"
                            description="Editá el bloque de presentación con su imagen lateral y el texto completo."
                        >
                            <Toggle
                                checked={form.intro.is_active}
                                onChange={(value) =>
                                    updateSection("intro", "is_active", value)
                                }
                                label="Mostrar bloque principal"
                            />

                            <Field label="Título">
                                <TextInput
                                    value={form.intro.title}
                                    onChange={(event) =>
                                        updateSection(
                                            "intro",
                                            "title",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Quienes somos"
                                />
                            </Field>

                            <Field
                                label="Contenido"
                                hint="Separá párrafos dejando una línea en blanco entre uno y otro."
                            >
                                <RichTextEditor
                                    value={form.intro.description}
                                    onChange={(event) =>
                                        updateSection(
                                            "intro",
                                            "description",
                                            event,
                                        )
                                    }
                                    placeholder="Contenido principal de la empresa..."
                                />
                            </Field>

                            <Field label="Imagen del contenido">
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) =>
                                        updateSection(
                                            "intro",
                                            "image_file",
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                />
                            </Field>

                            <div className="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-50">
                                {introPreview ? (
                                    <img
                                        src={introPreview}
                                        alt={form.intro.title || "Nosotros"}
                                        className="h-[320px] w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex h-[320px] items-center justify-center px-6 text-center text-sm text-slate-500">
                                        Subí la imagen del bloque principal.
                                    </div>
                                )}
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon="solar:target-outline"
                            badge="Misión y Visión"
                            title="Misión y visión"
                            description="Administrá ambos bloques institucionales con sus textos completos."
                        >
                            <Toggle
                                checked={form.missionVision.is_active}
                                onChange={(value) =>
                                    updateSection(
                                        "missionVision",
                                        "is_active",
                                        value,
                                    )
                                }
                                label="Mostrar sección de misión y visión"
                            />

                            <div className="grid gap-5 xl:grid-cols-2">
                                {form.missionVision.items.map((item, index) => (
                                    <div
                                        key={item.item_key || index}
                                        className="rounded-[24px] border border-slate-200 bg-slate-50 p-5"
                                    >
                                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                            <h3 className="text-lg font-semibold text-slate-900">
                                                {index === 0 ? "Misión" : "Visión"}
                                            </h3>
                                            <Toggle
                                                checked={item.is_active}
                                                onChange={(value) =>
                                                    updateItem(
                                                        "missionVision",
                                                        index,
                                                        "is_active",
                                                        value,
                                                    )
                                                }
                                                label="Visible"
                                            />
                                        </div>

                                        <div className="space-y-4">
                                            <Field label="Título">
                                                <TextInput
                                                    value={item.title}
                                                    onChange={(event) =>
                                                        updateItem(
                                                            "missionVision",
                                                            index,
                                                            "title",
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                            </Field>

                                            <Field label="Contenido">
                                                <RichTextEditor
                                                    value={item.description}
                                                    onChange={(value) =>
                                                        updateItem(
                                                            "missionVision",
                                                            index,
                                                            "description",
                                                            value,
                                                        )
                                                    }
                                                    placeholder={`Contenido de ${index === 0 ? "misión" : "visión"}`}
                                                />
                                            </Field>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon="solar:star-outline"
                            badge="Valores"
                            title="Valores"
                            description="Los íconos quedan como en la web pública; acá editás los títulos y qué valores se muestran."
                        >
                            <div className="flex flex-wrap items-center gap-3">
                                <Toggle
                                    checked={form.values.is_active}
                                    onChange={(value) =>
                                        updateSection(
                                            "values",
                                            "is_active",
                                            value,
                                        )
                                    }
                                    label="Mostrar sección de valores"
                                />
                            </div>

                            <Field label="Título de la sección">
                                <TextInput
                                    value={form.values.title}
                                    onChange={(event) =>
                                        updateSection(
                                            "values",
                                            "title",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Nuestros valores"
                                />
                            </Field>

                            <div className="grid gap-4 md:grid-cols-2">
                                {form.values.items.map((item, index) => (
                                    <div
                                        key={item.item_key || index}
                                        className="rounded-[24px] border border-slate-200 bg-slate-50 p-5"
                                    >
                                        <div className="mb-4 flex items-center justify-between gap-3">
                                            <p className="text-sm font-semibold uppercase tracking-[0.14em] text-slate-400">
                                                Valor {index + 1}
                                            </p>
                                            <Toggle
                                                checked={item.is_active}
                                                onChange={(value) =>
                                                    updateItem(
                                                        "values",
                                                        index,
                                                        "is_active",
                                                        value,
                                                    )
                                                }
                                                label="Visible"
                                            />
                                        </div>

                                        <Field label="Título">
                                            <TextInput
                                                value={item.title}
                                                onChange={(event) =>
                                                    updateItem(
                                                        "values",
                                                        index,
                                                        "title",
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                        </Field>
                                    </div>
                                ))}
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon="solar:buildings-3-outline"
                            badge="Pertenecemos a"
                            title="Pertenecemos a"
                            description="Cargá y administrá los logos institucionales que aparecen al final de la página."
                        >
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <Toggle
                                    checked={form.memberships.is_active}
                                    onChange={(value) =>
                                        updateSection(
                                            "memberships",
                                            "is_active",
                                            value,
                                        )
                                    }
                                    label="Mostrar sección de pertenencias"
                                />

                                <button
                                    type="button"
                                    onClick={addMembership}
                                    className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                >
                                    <Icon icon="solar:add-circle-outline" width={18} />
                                    Agregar institución
                                </button>
                            </div>

                            <Field label="Título de la sección">
                                <TextInput
                                    value={form.memberships.title}
                                    onChange={(event) =>
                                        updateSection(
                                            "memberships",
                                            "title",
                                            event.target.value,
                                        )
                                    }
                                    placeholder="Pertenecemos a"
                                />
                            </Field>

                            <div className="space-y-4">
                                {form.memberships.items.map((item, index) => (
                                    <div
                                        key={`${item.item_key || "membership"}-${index}`}
                                        className="rounded-[24px] border border-slate-200 bg-slate-50 p-5"
                                    >
                                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                            <p className="text-sm font-semibold text-slate-900">
                                                Institución {index + 1}
                                            </p>
                                            <div className="flex flex-wrap items-center gap-3">
                                                <Toggle
                                                    checked={item.is_active}
                                                    onChange={(value) =>
                                                        updateItem(
                                                            "memberships",
                                                            index,
                                                            "is_active",
                                                            value,
                                                        )
                                                    }
                                                    label="Visible"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        removeMembership(index)
                                                    }
                                                    className="inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                                >
                                                    <Icon icon="solar:trash-bin-trash-outline" width={18} />
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>

                                        <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px]">
                                            <div className="space-y-4">
                                                <Field label="Nombre">
                                                    <TextInput
                                                        value={item.title}
                                                        onChange={(event) =>
                                                            updateItem(
                                                                "memberships",
                                                                index,
                                                                "title",
                                                                event.target.value,
                                                            )
                                                        }
                                                        placeholder="Nombre de la institución"
                                                    />
                                                </Field>

                                                <Field label="Logo">
                                                    <input
                                                        type="file"
                                                        accept="image/*"
                                                        onChange={(event) =>
                                                            updateItem(
                                                                "memberships",
                                                                index,
                                                                "image_file",
                                                                event.target
                                                                    .files?.[0] ??
                                                                    null,
                                                            )
                                                        }
                                                        className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                                    />
                                                </Field>
                                            </div>

                                            <div className="overflow-hidden rounded-[22px] border border-slate-200 bg-white">
                                                {item.image_file ? (
                                                    <div className="flex h-[180px] items-center justify-center px-4 text-center text-sm text-slate-500">
                                                        {item.image_file.name}
                                                    </div>
                                                ) : item.media_url ? (
                                                    <img
                                                        src={item.media_url}
                                                        alt={item.title || "Logo"}
                                                        className="h-[180px] w-full object-contain p-5"
                                                    />
                                                ) : (
                                                    <div className="flex h-[180px] items-center justify-center px-4 text-center text-sm text-slate-500">
                                                        Sin logo cargado todavía.
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </SectionCard>
                    </div>

                    <aside className="space-y-6 xl:sticky xl:top-6 xl:self-start">
                        <section className="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                Resumen
                            </p>
                            <h2 className="mt-3 text-xl font-semibold text-slate-900">
                                Qué estás editando
                            </h2>
                            <div className="mt-5 space-y-3 text-sm leading-6 text-slate-600">
                                <p>
                                    El banner superior de la página pública.
                                </p>
                                <p>
                                    El bloque principal de presentación con su
                                    imagen.
                                </p>
                                <p>
                                    Misión, visión, valores y logos de
                                    pertenencia.
                                </p>
                            </div>
                        </section>

                        <section className="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-200 px-6 py-5">
                                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Vista rápida
                                </p>
                                <h2 className="mt-2 text-xl font-semibold text-slate-900">
                                    Estado actual
                                </h2>
                            </div>

                            <div className="space-y-4 px-6 py-5 text-sm text-slate-600">
                                <div className="flex items-center justify-between gap-4">
                                    <span>Banner</span>
                                    <span className={form.hero.is_active ? "font-semibold text-emerald-600" : "font-semibold text-slate-400"}>
                                        {form.hero.is_active ? "Visible" : "Oculto"}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <span>Contenido principal</span>
                                    <span className={form.intro.is_active ? "font-semibold text-emerald-600" : "font-semibold text-slate-400"}>
                                        {form.intro.is_active ? "Visible" : "Oculto"}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <span>Misión y visión</span>
                                    <span className={form.missionVision.is_active ? "font-semibold text-emerald-600" : "font-semibold text-slate-400"}>
                                        {form.missionVision.is_active ? "Visible" : "Oculto"}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <span>Valores</span>
                                    <span className={form.values.is_active ? "font-semibold text-emerald-600" : "font-semibold text-slate-400"}>
                                        {form.values.is_active ? "Visible" : "Oculto"}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <span>Pertenecemos a</span>
                                    <span className={form.memberships.is_active ? "font-semibold text-emerald-600" : "font-semibold text-slate-400"}>
                                        {form.memberships.is_active ? "Visible" : "Oculto"}
                                    </span>
                                </div>
                            </div>

                            <div className="border-t border-slate-200 px-6 py-5">
                                <button
                                    type="submit"
                                    disabled={saving}
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                >
                                    <Icon icon="solar:diskette-outline" width={18} />
                                    {saving
                                        ? "Guardando cambios..."
                                        : "Guardar página"}
                                </button>
                            </div>
                        </section>
                    </aside>
                </div>
            </form>
        </AdminLayout>
    );
}
