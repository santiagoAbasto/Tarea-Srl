import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useMemo, useState } from "react";

const PRODUCT_IMAGE_GUIDE = {
    title: "Imagen del producto",
    detail: "Usá una imagen de 391 x 391 px.",
    limit: "Peso máximo: 10 MB.",
    note: "Si respetás esa medida, la imagen del producto se va a ver mejor en el catálogo.",
};

const PRODUCT_BRAND_LOGO_GUIDE = {
    title: "Logo dentro de la imagen",
    detail: "Usá un logo de 77 x 44 px.",
    limit: "Peso máximo: 10 MB.",
    note: "Si mantenés esa proporción, el logo se va a ver más prolijo dentro de la imagen.",
};

const PRODUCTS_COVER_GUIDE = {
    title: "Imagen de la portada",
    detail: "Usá una imagen de 1366 x 350 px.",
    limit: "Peso máximo: 10 MB.",
    note: "Si respetás esa medida, la portada se va a ver mejor al abrir la página de productos.",
};

const PRODUCT_FAMILY_GUIDE = {
    title: "Imagen de la familia",
    detail: "Usá una imagen de 392 x 470 px.",
    limit: "Peso máximo: 10 MB.",
    note: "Si respetás esa medida, las familias se van a ver más ordenadas en el catálogo.",
};

function mediaUrlFromAsset(asset) {
    if (!asset?.path) {
        return "";
    }

    return `/media/${asset.path}`;
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

function normalizeSpecLabel(value) {
    return String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[øØ]/g, "o")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, " ")
        .replace(/\s+/g, " ")
        .trim();
}

function specChildLabel(value) {
    const stripped = String(value || "")
        .trim()
        .replace(/^\s*rango\s+/i, "")
        .replace(/^\s*[øØo]?\s*c?\s*cable\s*/i, "");
    const key = normalizeSpecLabel(stripped);

    if (key === "max") {
        return "máx";
    }

    if (["cu", "cobre", "al ac", "al acsr", "alac"].includes(key)) {
        return "Cu";
    }

    if (["ai", "al", "aluminio"].includes(key)) {
        return "Al";
    }

    return stripped;
}

function groupedSpecLayout(columns) {
    let rangeStart = null;
    let cableStart = null;

    columns.forEach((column, index) => {
        const next = columns[index + 1];

        if (!next) {
            return;
        }

        if (
            rangeStart === null &&
            normalizeSpecLabel(column.label).startsWith("rango") &&
            normalizeSpecLabel(next.label).startsWith("rango")
        ) {
            rangeStart = index;
        }

        if (
            cableStart === null &&
            /^(?:o\s*c?\s*)?cable\b/.test(normalizeSpecLabel(column.label)) &&
            /^(?:o\s*c?\s*)?cable\b/.test(normalizeSpecLabel(next.label))
        ) {
            cableStart = index;
        }
    });

    const grouped = rangeStart !== null && cableStart !== null && rangeStart < cableStart;

    return {
        grouped,
        rangeStart,
        cableStart,
        rangeUnit: grouped ? columns[rangeStart]?.unit || columns[rangeStart + 1]?.unit || "" : "",
        cableUnit: grouped ? columns[cableStart]?.unit || columns[cableStart + 1]?.unit || "" : "",
        subheaderIndexes: grouped
            ? [rangeStart, rangeStart + 1, cableStart, cableStart + 1]
            : [],
    };
}

function technicalSheetColumnsTemplate() {
    return [
        { label: "CÓDIGO", unit: "" },
        { label: "RANGO Al", unit: "mm²" },
        { label: "RANGO Cu", unit: "mm²" },
        { label: "øCABLE min", unit: "mm" },
        { label: "øCABLE max", unit: "mm" },
        { label: "A", unit: "mm²" },
        { label: "B", unit: "mm" },
        { label: "C", unit: "pulg." },
        { label: "D", unit: "mm" },
        { label: "CRMT", unit: "daN" },
        { label: "PESO", unit: "kg" },
    ];
}

function technicalSheetPendulumColumnsTemplate() {
    return [
        { label: "CÓDIGO", unit: "" },
        { label: "MODELO", unit: "" },
        { label: "RANGO Aluminio", unit: "mm²" },
        { label: "RANGO Aluminio/Acero", unit: "mm²" },
        { label: "øC Cable Minimo", unit: "mm" },
        { label: "øC Cable Maximo", unit: "mm" },
        { label: "A", unit: "mm" },
        { label: "B", unit: "mm" },
        { label: "Péndulo", unit: "" },
        { label: "Apto Contrapeso", unit: "" },
        { label: "CRMT", unit: "daN" },
        { label: "PESO", unit: "kg" },
    ];
}

function emptyHeroForm(hero) {
    return {
        id: hero?.id ?? null,
        title: hero?.title ?? "",
        media_id: hero?.media_id ?? null,
        media_url: hero?.media_url ?? "",
        image_file: null,
        is_active: !!hero?.is_active,
        sort_order: hero?.sort_order ?? "A",
    };
}

function emptyFamilyForm(family = null) {
    return {
        id: family?.id ?? null,
        name: family?.name ?? "",
        slug: family?.slug ?? "",
        description: family?.description ?? "",
        cover_media_id: family?.cover_media_id ?? null,
        cover_media_url: family?.cover_media_url ?? "",
        cover_file: null,
        banner_media_id: family?.banner_media_id ?? null,
        banner_media_url: family?.banner_media_url ?? "",
        banner_file: null,
        accent_color: family?.accent_color ?? "",
        sort_order: family?.sort_order ?? "A",
        is_active: family?.is_active ?? true,
        show_on_products_page: family?.show_on_products_page ?? true,
        show_on_home: family?.show_on_home ?? false,
    };
}

function emptySubfamilyForm(subfamily = null) {
    return {
        id: subfamily?.id ?? null,
        product_family_id: subfamily?.product_family_id ?? "",
        name: subfamily?.name ?? "",
        slug: subfamily?.slug ?? "",
        short_description: subfamily?.short_description ?? "",
        description: subfamily?.description ?? "",
        accent_color: subfamily?.accent_color ?? "",
        sort_order: subfamily?.sort_order ?? "A",
        is_active: subfamily?.is_active ?? true,
        show_on_home: subfamily?.show_on_home ?? false,
        show_on_family_page: subfamily?.show_on_family_page ?? true,
    };
}

function emptySpecTable() {
    const columns = technicalSheetColumnsTemplate();

    return {
        title: "Tabla técnica",
        columns,
        rows: [{ values: columns.map(() => "") }],
    };
}

function emptyProductForm(product = null) {
    return {
        id: product?.id ?? null,
        product_family_id: product?.product_family_id ?? "",
        product_subfamily_id: product?.product_subfamily_id ?? "",
        name: product?.name ?? "",
        slug: product?.slug ?? "",
        sku: product?.sku ?? "",
        short_description: product?.short_description ?? "",
        description: product?.description ?? "",
        material: product?.material ?? "",
        treatment: product?.treatment ?? "",
        observations: product?.observations ?? "",
        main_media_id: product?.main_media_id ?? null,
        main_media_url: product?.main_media_url ?? "",
        main_media_file: null,
        brand_logo_media_id: product?.brand_logo_media_id ?? null,
        brand_logo_media_url: product?.brand_logo_media_url ?? "",
        brand_logo_media_file: null,
        technical_sheet_media_id: product?.technical_sheet_media_id ?? null,
        technical_sheet_url: product?.technical_sheet_url ?? "",
        technical_sheet_file: null,
        sort_order: product?.sort_order ?? "A",
        is_active: product?.is_active ?? true,
        is_featured_home: product?.is_featured_home ?? false,
        is_featured_family: product?.is_featured_family ?? false,
        gallery: product?.gallery ?? [],
        related_product_ids: product?.related_product_ids ?? [],
        spec_tables: product?.spec_tables?.length ? product.spec_tables : [emptySpecTable()],
    };
}

function mapProductDetails(product) {
    const specTableRows = product?.spec_tables ?? product?.specTables ?? [];
    const mainMedia = product?.main_media ?? product?.mainMedia;
    const brandLogoMedia = product?.brand_logo_media ?? product?.brandLogoMedia;
    const technicalSheet = product?.technical_sheet ?? product?.technicalSheet;
    const relatedProducts = product?.related_products ?? product?.relatedProducts ?? [];

    const specTables = specTableRows.map((table) => {
        const columns = [...(table.columns ?? [])].sort((left, right) =>
            String(left.sort_order || "").localeCompare(String(right.sort_order || "")),
        );
        const rows = [...(table.rows ?? [])].sort((left, right) =>
            String(left.sort_order || "").localeCompare(String(right.sort_order || "")),
        );

        return {
            title: table.title ?? "",
            columns: columns.map((column) => ({
                label: column.label ?? "",
                unit: column.unit ?? "",
            })),
            rows: rows.map((row) => {
                if (
                    Array.isArray(row.values) &&
                    row.values.length > 0 &&
                    (typeof row.values[0] !== "object" || row.values[0] === null)
                ) {
                    return {
                        values: columns.map(
                            (_, columnIndex) => String(row.values?.[columnIndex] ?? ""),
                        ),
                    };
                }

                const valuesByColumn = new Map(
                    (row.values ?? []).map((value) => [
                        value.product_spec_column_id ?? value.column?.id,
                        value.value ?? "",
                    ]),
                );

                return {
                    values: columns.map((column) => valuesByColumn.get(column.id) ?? ""),
                };
            }),
        };
    });

    return emptyProductForm({
        ...product,
        main_media_url: mediaUrlFromAsset(mainMedia),
        brand_logo_media_url: mediaUrlFromAsset(brandLogoMedia),
        technical_sheet_url: mediaUrlFromAsset(technicalSheet),
        gallery: (product?.media ?? []).map((item) => ({
            media_id: item.media_id,
            url: mediaUrlFromAsset(item.media),
            file: null,
        })),
        related_product_ids: relatedProducts.map((item) => item.id),
        spec_tables: specTables,
    });
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

function ModalShell({ open, title, children, onClose, maxWidth = "max-w-5xl" }) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/45 p-4">
            <button
                type="button"
                aria-label="Cerrar modal"
                className="absolute inset-0"
                onClick={onClose}
            />

            <div
                className={`relative z-[121] flex max-h-[92vh] w-full ${maxWidth} flex-col overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.28)]`}
            >
                <div className="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
                    <button
                        type="button"
                        onClick={onClose}
                        className="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <Icon icon="solar:close-circle-outline" width={18} />
                    </button>
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto p-6">{children}</div>
            </div>
        </div>
    );
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
            className={`min-h-[120px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10 ${
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

function StatCard({ label, value, icon }) {
    return (
        <article className="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
            <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#117a98]">
                <Icon icon={icon} width={20} />
            </div>
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-2 text-3xl font-semibold text-slate-900">{value}</p>
        </article>
    );
}

function TabButton({ active, icon, label, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition ${
                active
                    ? "bg-[#25A7CA] text-white shadow-sm"
                    : "border border-slate-200 bg-white text-slate-600 hover:border-[#25A7CA] hover:text-[#117a98]"
            }`}
        >
            <Icon icon={icon} width={18} />
            {label}
        </button>
    );
}

function EmptyState({ title, description }) {
    return (
        <div className="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
            <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
            <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                {description}
            </p>
        </div>
    );
}

function CoverEditor({ form, onChange, onSave, saving }) {
    const [previewUrl, setPreviewUrl] = useState(form.media_url || "");

    useEffect(() => {
        if (!form.image_file) {
            setPreviewUrl(form.media_url || "");
            return undefined;
        }

        const nextUrl = URL.createObjectURL(form.image_file);
        setPreviewUrl(nextUrl);

        return () => URL.revokeObjectURL(nextUrl);
    }, [form.image_file, form.media_url]);

    return (
        <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
                <div className="space-y-5">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Portada pública
                        </p>
                        <h2 className="mt-3 text-2xl font-semibold text-slate-900">
                            Banner de /productos
                        </h2>
                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            Administrá el título y la imagen principal de la página
                            pública de productos.
                        </p>
                    </div>

                    <Toggle
                        checked={form.is_active}
                        onChange={(value) => onChange("is_active", value)}
                        label="Mostrar banner"
                    />

                    <Field label="Título principal">
                        <TextInput
                            value={form.title}
                            onChange={(event) => onChange("title", event.target.value)}
                            placeholder="PRODUCTOS"
                        />
                    </Field>

                    <Field label="Imagen del banner">
                        <div className="mb-4 overflow-hidden rounded-[24px] border border-[#25A7CA]/20 bg-[linear-gradient(135deg,rgba(37,167,202,0.18),rgba(255,255,255,0.98))] shadow-[0_18px_45px_rgba(37,167,202,0.12)]">
                            <div className="flex flex-col gap-4 p-4">
                                <div className="flex items-start gap-3">
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-[#117a98] shadow-sm">
                                        <Icon icon="solar:gallery-outline" width={22} />
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#117a98]">
                                            Recomendación importante
                                        </p>
                                        <h3 className="mt-1 text-base font-semibold text-slate-900">
                                            {PRODUCTS_COVER_GUIDE.title}
                                        </h3>
                                        <p className="mt-2 text-sm font-medium text-slate-700">
                                            {PRODUCTS_COVER_GUIDE.detail}
                                        </p>
                                        <p className="mt-1 text-sm font-semibold text-slate-900">
                                            {PRODUCTS_COVER_GUIDE.limit}
                                        </p>
                                        <p className="mt-2 text-sm text-slate-600">
                                            {PRODUCTS_COVER_GUIDE.note}
                                        </p>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-white/70 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                                    Tamaño recomendado
                                    <div className="mt-1 text-lg font-bold text-[#117a98]">
                                        1366 x 350 px
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(event) =>
                                onChange("image_file", event.target.files?.[0] ?? null)
                            }
                            className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                        />
                    </Field>

                    <button
                        type="button"
                        onClick={onSave}
                        disabled={saving}
                        className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                    >
                        <Icon icon="solar:diskette-outline" width={18} />
                        {saving ? "Guardando..." : "Guardar portada"}
                    </button>
                </div>

                <div className="overflow-hidden rounded-[28px] border border-slate-200 bg-slate-50">
                    {previewUrl ? (
                        <img
                            src={previewUrl}
                            alt={form.title || "Productos"}
                            className="h-[360px] w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-[360px] items-center justify-center px-6 text-center text-sm text-slate-500">
                            Subí una imagen para la portada de productos.
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}

function FamilyModal({ open, families, family, onClose, onSaved }) {
    const [form, setForm] = useState(emptyFamilyForm(family));
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setForm(emptyFamilyForm(family));
    }, [family, open]);

    const submit = async (event) => {
        event.preventDefault();
        setSaving(true);

        try {
            let coverMediaId = form.cover_media_id;
            let bannerMediaId = form.banner_media_id;

            if (form.cover_file) {
                const uploaded = await uploadAsset(
                    form.cover_file,
                    `${form.name || "familia"} cover`,
                );
                coverMediaId = uploaded.id;
            }

            if (form.banner_file) {
                const uploaded = await uploadAsset(
                    form.banner_file,
                    `${form.name || "familia"} banner`,
                );
                bannerMediaId = uploaded.id;
            }

            const payload = {
                name: form.name,
                slug: form.slug || slugify(form.name),
                description: form.description || null,
                cover_media_id: coverMediaId,
                banner_media_id: bannerMediaId,
                accent_color: form.accent_color || null,
                sort_order: form.sort_order || null,
                is_active: form.is_active,
                show_on_products_page: form.show_on_products_page,
                show_on_home: form.show_on_home,
            };

            if (form.id) {
                await axios.put(`/admin/api/product-families/${form.id}`, payload);
            } else {
                await axios.post("/admin/api/product-families", payload);
            }

            emitAdminToast(
                form.id
                    ? "La familia se actualizó correctamente."
                    : "La familia se creó correctamente.",
            );
            onSaved();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la familia.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <ModalShell
            open={open}
            onClose={onClose}
            title={form.id ? "Editar familia" : "Nueva familia"}
        >
            <form onSubmit={submit} className="space-y-5">
                <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Nombre">
                        <TextInput
                            value={form.name}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    name: event.target.value,
                                    slug:
                                        current.id || current.slug
                                            ? current.slug
                                            : slugify(event.target.value),
                                }))
                            }
                        />
                    </Field>

                    <Field label="Slug">
                        <TextInput
                            value={form.slug}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    slug: slugify(event.target.value),
                                }))
                            }
                        />
                    </Field>
                </div>

                <Field label="Descripción">
                    <TextArea
                        value={form.description}
                        onChange={(event) =>
                            setForm((current) => ({
                                ...current,
                                description: event.target.value,
                            }))
                        }
                        rows={5}
                    />
                </Field>

                <div className="grid gap-5 md:grid-cols-3">
                    <Field label="Color de acento">
                        <TextInput
                            value={form.accent_color}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    accent_color: event.target.value,
                                }))
                            }
                            placeholder="#093C62"
                        />
                    </Field>

                    <Field label="Orden">
                        <TextInput
                            value={form.sort_order}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    sort_order: event.target.value.toUpperCase(),
                                }))
                            }
                        />
                    </Field>
                </div>

                <div className="flex flex-wrap gap-3">
                    <Toggle
                        checked={form.is_active}
                        onChange={(value) =>
                            setForm((current) => ({ ...current, is_active: value }))
                        }
                        label="Activa"
                    />
                    <Toggle
                        checked={form.show_on_products_page}
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                show_on_products_page: value,
                            }))
                        }
                        label="Mostrar en /productos"
                    />
                    <Toggle
                        checked={form.show_on_home}
                        onChange={(value) =>
                            setForm((current) => ({ ...current, show_on_home: value }))
                        }
                        label="Mostrar en home"
                    />
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Imagen de card">
                        <div className="mb-4 overflow-hidden rounded-[24px] border border-[#25A7CA]/20 bg-[linear-gradient(135deg,rgba(37,167,202,0.18),rgba(255,255,255,0.98))] shadow-[0_18px_45px_rgba(37,167,202,0.12)]">
                            <div className="flex flex-col gap-4 p-4">
                                <div className="flex items-start gap-3">
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-[#117a98] shadow-sm">
                                        <Icon icon="solar:gallery-outline" width={22} />
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#117a98]">
                                            Recomendación importante
                                        </p>
                                        <h3 className="mt-1 text-base font-semibold text-slate-900">
                                            {PRODUCT_FAMILY_GUIDE.title}
                                        </h3>
                                        <p className="mt-2 text-sm font-medium text-slate-700">
                                            {PRODUCT_FAMILY_GUIDE.detail}
                                        </p>
                                        <p className="mt-1 text-sm font-semibold text-slate-900">
                                            {PRODUCT_FAMILY_GUIDE.limit}
                                        </p>
                                        <p className="mt-2 text-sm text-slate-600">
                                            {PRODUCT_FAMILY_GUIDE.note}
                                        </p>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-white/70 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                                    Tamaño recomendado
                                    <div className="mt-1 text-lg font-bold text-[#117a98]">
                                        392 x 470 px
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    cover_file: event.target.files?.[0] ?? null,
                                }))
                            }
                            className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                        />
                        {form.cover_media_url ? (
                            <img
                                src={form.cover_media_url}
                                alt={form.name || "Cover"}
                                className="mt-3 h-44 w-full rounded-2xl border border-slate-200 object-cover"
                            />
                        ) : null}
                    </Field>

                    <Field label="Banner de familia">
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    banner_file: event.target.files?.[0] ?? null,
                                }))
                            }
                            className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                        />
                        {form.banner_media_url ? (
                            <img
                                src={form.banner_media_url}
                                alt={form.name || "Banner"}
                                className="mt-3 h-44 w-full rounded-2xl border border-slate-200 object-cover"
                            />
                        ) : null}
                    </Field>
                </div>

                <div className="flex justify-end gap-3 border-t border-slate-200 pt-5">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        disabled={saving}
                        className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                    >
                        {saving ? "Guardando..." : form.id ? "Guardar cambios" : "Crear familia"}
                    </button>
                </div>
            </form>
        </ModalShell>
    );
}

function SubfamilyModal({ open, subfamily, families, onClose, onSaved }) {
    const [form, setForm] = useState(emptySubfamilyForm(subfamily));
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setForm(emptySubfamilyForm(subfamily));
    }, [subfamily, open]);

    const submit = async (event) => {
        event.preventDefault();
        setSaving(true);

        try {
            const payload = {
                product_family_id: Number(form.product_family_id),
                name: form.name,
                slug: form.slug || slugify(form.name),
                short_description: form.short_description || null,
                description: form.description || null,
                accent_color: form.accent_color || null,
                sort_order: form.sort_order || null,
                is_active: form.is_active,
                show_on_home: form.show_on_home,
                show_on_family_page: form.show_on_family_page,
            };

            if (form.id) {
                await axios.put(
                    `/admin/api/product-subfamilies/${form.id}`,
                    payload,
                );
            } else {
                await axios.post("/admin/api/product-subfamilies", payload);
            }

            emitAdminToast(
                form.id
                    ? "La subfamilia se actualizó correctamente."
                    : "La subfamilia se creó correctamente.",
            );
            onSaved();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la subfamilia.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <ModalShell
            open={open}
            onClose={onClose}
            title={form.id ? "Editar subfamilia" : "Nueva subfamilia"}
        >
            <form onSubmit={submit} className="space-y-5">
                <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Familia">
                        <select
                            value={form.product_family_id}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    product_family_id: event.target.value,
                                }))
                            }
                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                        >
                            <option value="">Seleccionar familia</option>
                            {families.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.name}
                                </option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Nombre">
                        <TextInput
                            value={form.name}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    name: event.target.value,
                                    slug:
                                        current.id || current.slug
                                            ? current.slug
                                            : slugify(event.target.value),
                                }))
                            }
                        />
                    </Field>
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Slug">
                        <TextInput
                            value={form.slug}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    slug: slugify(event.target.value),
                                }))
                            }
                        />
                    </Field>

                    <Field label="Descripción corta">
                        <TextInput
                            value={form.short_description}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    short_description: event.target.value,
                                }))
                            }
                        />
                    </Field>
                </div>

                <Field label="Descripción">
                    <TextArea
                        value={form.description}
                        onChange={(event) =>
                            setForm((current) => ({
                                ...current,
                                description: event.target.value,
                            }))
                        }
                        rows={5}
                    />
                </Field>

                <div className="grid gap-5 md:grid-cols-3">
                    <Field label="Color de acento">
                        <TextInput
                            value={form.accent_color}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    accent_color: event.target.value,
                                }))
                            }
                            placeholder="#8BCFDE"
                        />
                    </Field>

                    <Field label="Orden">
                        <TextInput
                            value={form.sort_order}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    sort_order: event.target.value.toUpperCase(),
                                }))
                            }
                        />
                    </Field>
                </div>

                <div className="flex flex-wrap gap-3">
                    <Toggle
                        checked={form.is_active}
                        onChange={(value) =>
                            setForm((current) => ({ ...current, is_active: value }))
                        }
                        label="Activa"
                    />
                    <Toggle
                        checked={form.show_on_home}
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                show_on_home: value,
                            }))
                        }
                        label="Mostrar en home"
                    />
                    <Toggle
                        checked={form.show_on_family_page}
                        onChange={(value) =>
                            setForm((current) => ({
                                ...current,
                                show_on_family_page: value,
                            }))
                        }
                        label="Mostrar en página de familia"
                    />
                </div>

                <div className="flex justify-end gap-3 border-t border-slate-200 pt-5">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        disabled={saving}
                        className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                    >
                        {saving ? "Guardando..." : form.id ? "Guardar cambios" : "Crear subfamilia"}
                    </button>
                </div>
            </form>
        </ModalShell>
    );
}

function GalleryEditor({ gallery, onChange }) {
    const removeAt = (index) => {
        onChange(gallery.filter((_, currentIndex) => currentIndex !== index));
    };

    const addFiles = (files) => {
        const nextItems = Array.from(files).map((file) => ({
            media_id: null,
            url: URL.createObjectURL(file),
            file,
        }));

        onChange([...gallery, ...nextItems]);
    };

    return (
        <div className="space-y-4">
            <input
                type="file"
                accept="image/*"
                multiple
                onChange={(event) => addFiles(event.target.files ?? [])}
                className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
            />

            {gallery.length ? (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {gallery.map((item, index) => (
                        <article
                            key={`${item.media_id || "new"}-${index}`}
                            className="overflow-hidden rounded-[22px] border border-slate-200 bg-slate-50"
                        >
                            {item.url ? (
                                <img
                                    src={item.url}
                                    alt={`Galería ${index + 1}`}
                                    className="h-40 w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-40 items-center justify-center px-4 text-center text-sm text-slate-500">
                                    Archivo cargado
                                </div>
                            )}
                            <div className="flex justify-end p-3">
                                <button
                                    type="button"
                                    onClick={() => removeAt(index)}
                                    className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                >
                                    Quitar
                                </button>
                            </div>
                        </article>
                    ))}
                </div>
            ) : null}
        </div>
    );
}

function SpecTablePreview({ table }) {
    const columns = (table.columns ?? [])
        .map((column, index) => ({ ...column, sourceIndex: index }))
        .filter((column) => String(column.label || "").trim() !== "");
    const rows = table.rows?.length ? table.rows : [{ values: [] }];
    const layout = groupedSpecLayout(columns);
    const mergeColumnKeys = ["pendulo"];

    const bodyCells = rows.map((row) => columns.map((column) => row.values?.[column.sourceIndex] || ""));
    const skipCells = {};
    const rowspans = {};

    columns.forEach((column, columnIndex) => {
        if (!mergeColumnKeys.includes(normalizeSpecLabel(column.label))) {
            return;
        }

        bodyCells.forEach((row, rowIndex) => {
            if (skipCells[`${rowIndex}-${columnIndex}`]) {
                return;
            }

            const value = String(row[columnIndex] || "").trim();
            let span = 1;

            if (value !== "") {
                for (let nextRowIndex = rowIndex + 1; nextRowIndex < bodyCells.length; nextRowIndex += 1) {
                    if (String(bodyCells[nextRowIndex]?.[columnIndex] || "").trim() !== value) {
                        break;
                    }

                    span += 1;
                    skipCells[`${nextRowIndex}-${columnIndex}`] = true;
                }
            }

            rowspans[`${rowIndex}-${columnIndex}`] = span;
        });
    });

    if (columns.length === 0) {
        return (
            <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500">
                Cargá columnas para ver la previsualización de la ficha técnica.
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
            <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p className="text-sm font-semibold text-slate-900">
                        Preview ficha técnica
                    </p>
                    <p className="text-xs text-slate-500">
                        Así se verá en la web pública y en el PDF.
                    </p>
                </div>
                {layout.grouped ? (
                    <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Formato agrupado activo
                    </span>
                ) : (
                    <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                        Formato simple
                    </span>
                )}
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full border-collapse text-center text-sm text-slate-800">
                    <thead className="bg-[#eef2f4]">
                        {layout.grouped ? (
                            <>
                                <tr>
                                    {columns.map((column, columnIndex) => {
                                        if (columnIndex === layout.rangeStart) {
                                            return (
                                                <th
                                                    key="preview-range-head"
                                                    colSpan={2}
                                                    className="border border-slate-300 px-4 py-2 font-bold uppercase"
                                                >
                                                    RANGO {layout.rangeUnit ? `[${layout.rangeUnit}]` : ""}
                                                </th>
                                            );
                                        }

                                        if (columnIndex === layout.rangeStart + 1) {
                                            return null;
                                        }

                                        if (columnIndex === layout.cableStart) {
                                            return (
                                                <th
                                                    key="preview-cable-head"
                                                    colSpan={2}
                                                    className="border border-slate-300 px-4 py-2 font-bold"
                                                >
                                                    øC Cable {layout.cableUnit ? `[${layout.cableUnit}]` : ""}
                                                </th>
                                            );
                                        }

                                        if (columnIndex === layout.cableStart + 1) {
                                            return null;
                                        }

                                        return (
                                            <th
                                                key={`preview-head-${column.sourceIndex}`}
                                                rowSpan={2}
                                                className="border border-slate-300 px-4 py-3 font-bold uppercase"
                                            >
                                                <span>{column.label}</span>
                                                {column.unit ? (
                                                    <small className="mt-1 block font-normal normal-case text-slate-600">
                                                        [{column.unit}]
                                                    </small>
                                                ) : null}
                                            </th>
                                        );
                                    })}
                                </tr>
                                <tr>
                                    {layout.subheaderIndexes.map((columnIndex) => (
                                        <th
                                            key={`preview-subhead-${columns[columnIndex]?.sourceIndex}`}
                                            className="border border-slate-300 px-4 py-2 font-bold"
                                        >
                                            {specChildLabel(columns[columnIndex]?.label)}
                                        </th>
                                    ))}
                                </tr>
                            </>
                        ) : (
                            <tr>
                                {columns.map((column) => (
                                    <th
                                        key={`preview-head-${column.sourceIndex}`}
                                        className="border border-slate-300 px-4 py-3 font-bold"
                                    >
                                        <span>{column.label}</span>
                                        {column.unit ? (
                                            <small className="mt-1 block font-normal text-slate-600">
                                                [{column.unit}]
                                            </small>
                                        ) : null}
                                    </th>
                                ))}
                            </tr>
                        )}
                    </thead>
                    <tbody>
                        {rows.map((row, rowIndex) => (
                            <tr key={`preview-row-${rowIndex}`}>
                                {columns.map((column, columnIndex) => {
                                    const cellKey = `${rowIndex}-${columnIndex}`;

                                    if (skipCells[cellKey]) {
                                        return null;
                                    }

                                    return (
                                        <td
                                            key={`preview-cell-${rowIndex}-${column.sourceIndex}`}
                                            rowSpan={rowspans[cellKey] > 1 ? rowspans[cellKey] : undefined}
                                            className="border border-slate-300 px-4 py-3"
                                        >
                                            {bodyCells[rowIndex]?.[columnIndex] || "-"}
                                        </td>
                                    );
                                })}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function SpecTablesEditor({ specTables, onChange }) {
    const updateTable = (tableIndex, updater) => {
        onChange(
            specTables.map((table, currentIndex) =>
                currentIndex === tableIndex ? updater(table) : table,
            ),
        );
    };

    const addTable = () => onChange([...specTables, emptySpecTable()]);

    const removeTable = (tableIndex) =>
        onChange(specTables.filter((_, index) => index !== tableIndex));

    const applyTechnicalSheetTemplate = (tableIndex) =>
        updateTable(tableIndex, (table) => {
            const columns = technicalSheetColumnsTemplate();
            const rows = table.rows?.length
                ? table.rows.map((row) => ({
                      ...row,
                      values: columns.map((_, columnIndex) => row.values?.[columnIndex] ?? ""),
                  }))
                : [{ values: columns.map(() => "") }];

            return {
                ...table,
                title: table.title || "Tabla técnica",
                columns,
                rows,
            };
        });

    const applyPendulumTechnicalSheetTemplate = (tableIndex) =>
        updateTable(tableIndex, (table) => {
            const columns = technicalSheetPendulumColumnsTemplate();
            const rows = table.rows?.length
                ? table.rows.map((row) => ({
                      ...row,
                      values: columns.map((_, columnIndex) => row.values?.[columnIndex] ?? ""),
                  }))
                : Array.from({ length: 8 }, () => ({ values: columns.map(() => "") }));

            return {
                ...table,
                title: table.title || "Tabla técnica",
                columns,
                rows,
            };
        });

    const addColumn = (tableIndex) =>
        updateTable(tableIndex, (table) => ({
            ...table,
            columns: [...table.columns, { label: "", unit: "" }],
            rows: table.rows.map((row) => ({
                ...row,
                values: [...row.values, ""],
            })),
        }));

    const removeColumn = (tableIndex, columnIndex) =>
        updateTable(tableIndex, (table) => ({
            ...table,
            columns: table.columns.filter((_, index) => index !== columnIndex),
            rows: table.rows.map((row) => ({
                ...row,
                values: row.values.filter((_, index) => index !== columnIndex),
            })),
        }));

    const addRow = (tableIndex) =>
        updateTable(tableIndex, (table) => ({
            ...table,
            rows: [
                ...table.rows,
                { values: table.columns.map(() => "") },
            ],
        }));

    const removeRow = (tableIndex, rowIndex) =>
        updateTable(tableIndex, (table) => ({
            ...table,
            rows: table.rows.filter((_, index) => index !== rowIndex),
        }));

    return (
        <div className="space-y-5">
            {specTables.map((table, tableIndex) => (
                <div
                    key={`table-${tableIndex}`}
                    className="rounded-[24px] border border-slate-200 bg-slate-50 p-5"
                >
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <Field label={`Tabla ${tableIndex + 1}`}>
                            <TextInput
                                value={table.title}
                                onChange={(event) =>
                                    updateTable(tableIndex, (current) => ({
                                        ...current,
                                        title: event.target.value,
                                    }))
                                }
                                placeholder="Tabla técnica"
                            />
                        </Field>
                        {specTables.length > 1 ? (
                            <button
                                type="button"
                                onClick={() => removeTable(tableIndex)}
                                className="rounded-2xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                            >
                                Eliminar tabla
                            </button>
                        ) : null}
                    </div>

                    <div className="space-y-4">
                        <div className="space-y-3">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-sm font-semibold text-slate-900">
                                    Columnas
                                </p>
                                <div className="flex flex-wrap justify-end gap-2">
                                    <button
                                        type="button"
                                        onClick={() => applyTechnicalSheetTemplate(tableIndex)}
                                        className="rounded-xl border border-[#25A7CA]/30 bg-[#25A7CA]/10 px-3 py-2 text-xs font-semibold text-[#117a98] transition hover:bg-[#25A7CA]/15"
                                    >
                                        Formato simple
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => applyPendulumTechnicalSheetTemplate(tableIndex)}
                                        className="rounded-xl border border-[#093E66]/25 bg-[#093E66]/10 px-3 py-2 text-xs font-semibold text-[#093E66] transition hover:bg-[#093E66]/15"
                                    >
                                        Formato con péndulo
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => addColumn(tableIndex)}
                                        className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                    >
                                        Agregar columna
                                    </button>
                                </div>
                            </div>

                            {table.columns.map((column, columnIndex) => (
                                <div
                                    key={`column-${columnIndex}`}
                                    className="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_auto]"
                                >
                                    <TextInput
                                        value={column.label}
                                        onChange={(event) =>
                                            updateTable(tableIndex, (current) => ({
                                                ...current,
                                                columns: current.columns.map(
                                                    (item, index) =>
                                                        index === columnIndex
                                                            ? {
                                                                  ...item,
                                                                  label: event.target.value,
                                                              }
                                                            : item,
                                                ),
                                            }))
                                        }
                                        placeholder="Nombre de columna"
                                    />
                                    <TextInput
                                        value={column.unit}
                                        onChange={(event) =>
                                            updateTable(tableIndex, (current) => ({
                                                ...current,
                                                columns: current.columns.map(
                                                    (item, index) =>
                                                        index === columnIndex
                                                            ? {
                                                                  ...item,
                                                                  unit: event.target.value,
                                                              }
                                                            : item,
                                                ),
                                            }))
                                        }
                                        placeholder="Unidad"
                                    />
                                    {table.columns.length > 1 ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                removeColumn(tableIndex, columnIndex)
                                            }
                                            className="rounded-2xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                        >
                                            Quitar
                                        </button>
                                    ) : (
                                        <div />
                                    )}
                                </div>
                            ))}
                        </div>

                        <div className="space-y-3">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="text-sm font-semibold text-slate-900">
                                        Datos de la tabla
                                    </p>
                                    <p className="mt-1 text-xs leading-5 text-slate-500">
                                        Cargá una fila por modelo. Para fusionar el péndulo
                                        como en la ficha, repetí exactamente el mismo texto en
                                        filas consecutivas, por ejemplo Fundido o Forjado.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => addRow(tableIndex)}
                                    className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                >
                                    Agregar fila
                                </button>
                            </div>

                            <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                                <table className="min-w-full border-collapse text-sm">
                                    <thead className="bg-slate-100 text-left text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <tr>
                                            <th className="w-16 border border-slate-200 px-3 py-3 font-semibold">
                                                Fila
                                            </th>
                                            {table.columns.map((column, columnIndex) => (
                                                <th
                                                    key={`sheet-head-${columnIndex}`}
                                                    className="min-w-[150px] border border-slate-200 px-3 py-3 font-semibold"
                                                >
                                                    <span>{column.label || `Columna ${columnIndex + 1}`}</span>
                                                    {column.unit ? (
                                                        <small className="mt-1 block normal-case tracking-normal text-slate-400">
                                                            [{column.unit}]
                                                        </small>
                                                    ) : null}
                                                </th>
                                            ))}
                                            <th className="w-24 border border-slate-200 px-3 py-3 font-semibold">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {table.rows.map((row, rowIndex) => (
                                            <tr key={`sheet-row-${rowIndex}`}>
                                                <td className="border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                                    {rowIndex + 1}
                                                </td>
                                                {table.columns.map((column, columnIndex) => (
                                                    <td
                                                        key={`sheet-cell-${rowIndex}-${columnIndex}`}
                                                        className="border border-slate-200 p-2"
                                                    >
                                                        <input
                                                            value={row.values?.[columnIndex] ?? ""}
                                                            onChange={(event) => {
                                                                const nextValue = event.target.value;

                                                                updateTable(tableIndex, (current) => ({
                                                                    ...current,
                                                                    rows: current.rows.map(
                                                                        (item, currentRowIndex) => {
                                                                            if (currentRowIndex !== rowIndex) {
                                                                                return item;
                                                                            }

                                                                            const values = current.columns.map(
                                                                                (_, currentColumnIndex) =>
                                                                                    item.values?.[currentColumnIndex] ?? "",
                                                                            );
                                                                            values[columnIndex] = nextValue;

                                                                            return {
                                                                                ...item,
                                                                                values,
                                                                            };
                                                                        },
                                                                    ),
                                                                }));
                                                            }}
                                                            placeholder={column.label || `Columna ${columnIndex + 1}`}
                                                            className="w-full min-w-[140px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                                        />
                                                    </td>
                                                ))}
                                                <td className="border border-slate-200 p-2 text-center">
                                                    {table.rows.length > 1 ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => removeRow(tableIndex, rowIndex)}
                                                            className="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                        >
                                                            Quitar
                                                        </button>
                                                    ) : (
                                                        <span className="text-xs text-slate-300">-</span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <SpecTablePreview table={table} />
                    </div>
                </div>
            ))}

            <button
                type="button"
                onClick={addTable}
                className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
            >
                Agregar otra tabla técnica
            </button>
        </div>
    );
}

function ProductModal({
    open,
    productId,
    families,
    subfamilies,
    products,
    onClose,
    onSaved,
}) {
    const [form, setForm] = useState(emptyProductForm());
    const [loading, setLoading] = useState(false);
    const [loadedProductId, setLoadedProductId] = useState(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (!open) {
            setLoading(false);
            setLoadedProductId(null);
            return;
        }

        if (!productId) {
            setForm(emptyProductForm());
            setLoadedProductId("new");
            setLoading(false);
            return;
        }

        let active = true;
        setLoading(true);
        setLoadedProductId(null);

        axios
            .get(`/admin/api/products/${productId}`)
            .then((response) => {
                if (active) {
                    setForm(mapProductDetails(response.data));
                    setLoadedProductId(productId);
                }
            })
            .catch(() => {
                emitAdminToast(
                    "No se pudo cargar el detalle del producto.",
                    "error",
                );
            })
            .finally(() => {
                if (active) {
                    setLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, [open, productId]);

    const waitingForProduct =
        open && Boolean(productId) && loadedProductId !== productId;

    const filteredSubfamilies = useMemo(
        () =>
            subfamilies.filter(
                (item) => Number(item.product_family_id) === Number(form.product_family_id),
            ),
        [subfamilies, form.product_family_id],
    );

    const submit = async (event) => {
        event.preventDefault();
        setSaving(true);

        try {
            let mainMediaId = form.main_media_id;
            let brandLogoMediaId = form.brand_logo_media_id;
            let technicalSheetMediaId = form.technical_sheet_media_id;

            if (form.main_media_file) {
                const uploaded = await uploadAsset(
                    form.main_media_file,
                    `${form.name || "producto"} principal`,
                );
                mainMediaId = uploaded.id;
            }

            if (form.technical_sheet_file) {
                const uploaded = await uploadAsset(
                    form.technical_sheet_file,
                    `${form.name || "producto"} ficha`,
                );
                technicalSheetMediaId = uploaded.id;
            }

            if (form.brand_logo_media_file) {
                const uploaded = await uploadAsset(
                    form.brand_logo_media_file,
                    `${form.name || "producto"} marca`,
                );
                brandLogoMediaId = uploaded.id;
            }

            const mediaRows = [];
            for (const [index, item] of form.gallery.entries()) {
                let mediaId = item.media_id;

                if (!mediaId && item.file) {
                    const uploaded = await uploadAsset(
                        item.file,
                        `${form.name || "producto"} galeria ${index + 1}`,
                    );
                    mediaId = uploaded.id;
                }

                if (!mediaId) {
                    continue;
                }

                mediaRows.push({
                    media_id: mediaId,
                    sort_order: String.fromCharCode(65 + index),
                    is_primary: false,
                });
            }

            const payload = {
                product_family_id: Number(form.product_family_id),
                product_subfamily_id: form.product_subfamily_id
                    ? Number(form.product_subfamily_id)
                    : null,
                name: form.name,
                slug: form.slug || slugify(form.name),
                sku: form.sku || null,
                short_description: form.short_description || null,
                description: form.description || null,
                material: form.material || null,
                treatment: form.treatment || null,
                observations: form.observations || null,
                main_media_id: mainMediaId,
                brand_logo_media_id: brandLogoMediaId,
                technical_sheet_media_id: technicalSheetMediaId,
                sort_order: form.sort_order || null,
                is_active: form.is_active,
                is_featured_home: form.is_featured_home,
                is_featured_family: form.is_featured_family,
                media: mediaRows,
                related_product_ids: form.related_product_ids,
                spec_tables: form.spec_tables
                    .filter(
                        (table) =>
                            table.columns.some((column) => column.label.trim() !== "") ||
                            table.rows.some((row) =>
                                row.values.some((value) => String(value || "").trim() !== ""),
                            ),
                    )
                    .map((table, tableIndex) => {
                        const columns = table.columns
                            .map((column, sourceIndex) => ({
                                ...column,
                                sourceIndex,
                            }))
                            .filter((column) => column.label.trim() !== "");

                        return {
                            title: table.title || null,
                            sort_order: String.fromCharCode(65 + tableIndex),
                            columns: columns.map((column, columnIndex) => ({
                                label: column.label,
                                unit: column.unit || null,
                                sort_order: String.fromCharCode(65 + columnIndex),
                            })),
                            rows: table.rows.map((row, rowIndex) => ({
                                sort_order: String.fromCharCode(65 + rowIndex),
                                values: columns.map(
                                    (column) => row.values?.[column.sourceIndex] ?? "",
                                ),
                            })),
                        };
                    }),
            };

            if (form.id) {
                await axios.put(`/admin/api/products/${form.id}`, payload);
            } else {
                await axios.post("/admin/api/products", payload);
            }

            emitAdminToast(
                form.id
                    ? "El producto se actualizó correctamente."
                    : "El producto se creó correctamente.",
            );
            onSaved();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar el producto.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <ModalShell
            open={open}
            onClose={onClose}
            title={form.id ? "Editar producto" : "Nuevo producto"}
            maxWidth="max-w-7xl"
        >
            {loading || waitingForProduct ? (
                <div className="py-20 text-center text-sm text-slate-500">
                    Cargando producto...
                </div>
            ) : (
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-5 xl:grid-cols-2">
                        <Field label="Familia">
                            <select
                                value={form.product_family_id}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        product_family_id: event.target.value,
                                        product_subfamily_id: "",
                                    }))
                                }
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            >
                                <option value="">Seleccionar familia</option>
                                {families.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.name}
                                    </option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Subfamilia">
                            <select
                                value={form.product_subfamily_id}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        product_subfamily_id: event.target.value,
                                    }))
                                }
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            >
                                <option value="">Sin subfamilia</option>
                                {filteredSubfamilies.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.name}
                                    </option>
                                ))}
                            </select>
                        </Field>
                    </div>

                    <div className="grid gap-5 xl:grid-cols-2">
                        <Field label="Nombre">
                            <TextInput
                                value={form.name}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        name: event.target.value,
                                        slug:
                                            current.id || current.slug
                                                ? current.slug
                                                : slugify(event.target.value),
                                    }))
                                }
                            />
                        </Field>

                        <Field label="Slug">
                            <TextInput
                                value={form.slug}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        slug: slugify(event.target.value),
                                    }))
                                }
                            />
                        </Field>
                    </div>

                    <div className="grid gap-5 xl:grid-cols-3">
                        <Field label="SKU">
                            <TextInput
                                value={form.sku}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        sku: event.target.value,
                                    }))
                                }
                            />
                        </Field>

                        <Field label="Descripción corta">
                            <TextInput
                                value={form.short_description}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        short_description: event.target.value,
                                    }))
                                }
                            />
                        </Field>

                        <Field label="Orden">
                            <TextInput
                                value={form.sort_order}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        sort_order: event.target.value.toUpperCase(),
                                    }))
                                }
                            />
                        </Field>
                    </div>

                    <Field label="Descripción completa">
                        <TextArea
                            value={form.description}
                            onChange={(event) =>
                                setForm((current) => ({
                                    ...current,
                                    description: event.target.value,
                                }))
                            }
                            rows={6}
                        />
                    </Field>

                    <div className="grid gap-5 xl:grid-cols-3">
                        <Field label="Material">
                            <TextArea
                                value={form.material}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        material: event.target.value,
                                    }))
                                }
                                rows={4}
                            />
                        </Field>

                        <Field label="Tratamiento">
                            <TextArea
                                value={form.treatment}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        treatment: event.target.value,
                                    }))
                                }
                                rows={4}
                            />
                        </Field>

                        <Field label="Observaciones">
                            <TextArea
                                value={form.observations}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        observations: event.target.value,
                                    }))
                                }
                                rows={4}
                            />
                        </Field>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Toggle
                            checked={form.is_active}
                            onChange={(value) =>
                                setForm((current) => ({ ...current, is_active: value }))
                            }
                            label="Activo"
                        />
                        <Toggle
                            checked={form.is_featured_home}
                            onChange={(value) =>
                                setForm((current) => ({
                                    ...current,
                                    is_featured_home: value,
                                }))
                            }
                            label="Destacado en home"
                        />
                        <Toggle
                            checked={form.is_featured_family}
                            onChange={(value) =>
                                setForm((current) => ({
                                    ...current,
                                    is_featured_family: value,
                                }))
                            }
                            label="Destacado en familia"
                        />
                    </div>

                    <div className="grid gap-5 xl:grid-cols-3">
                        <Field label="Imagen principal">
                            <div className="mb-4 overflow-hidden rounded-[24px] border border-[#25A7CA]/20 bg-[linear-gradient(135deg,rgba(37,167,202,0.18),rgba(255,255,255,0.98))] shadow-[0_18px_45px_rgba(37,167,202,0.12)]">
                                <div className="flex flex-col gap-4 p-4">
                                    <div className="flex items-start gap-3">
                                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-[#117a98] shadow-sm">
                                            <Icon icon="solar:gallery-outline" width={22} />
                                        </div>
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#117a98]">
                                                Recomendación importante
                                            </p>
                                            <h3 className="mt-1 text-base font-semibold text-slate-900">
                                                {PRODUCT_IMAGE_GUIDE.title}
                                            </h3>
                                            <p className="mt-2 text-sm font-medium text-slate-700">
                                                {PRODUCT_IMAGE_GUIDE.detail}
                                            </p>
                                            <p className="mt-1 text-sm font-semibold text-slate-900">
                                                {PRODUCT_IMAGE_GUIDE.limit}
                                            </p>
                                            <p className="mt-2 text-sm text-slate-600">
                                                {PRODUCT_IMAGE_GUIDE.note}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="rounded-2xl border border-white/70 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                                        Tamaño recomendado
                                        <div className="mt-1 text-lg font-bold text-[#117a98]">
                                            391 x 391 px
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        main_media_file:
                                            event.target.files?.[0] ?? null,
                                    }))
                                }
                                className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                            />
                            {form.main_media_url ? (
                                <img
                                    src={form.main_media_url}
                                    alt={form.name || "Principal"}
                                    className="mt-3 h-56 w-full rounded-2xl border border-slate-200 object-cover"
                                />
                            ) : null}
                        </Field>

                        <Field
                            label="Logo de marca"
                            hint="Se usa en la card de productos destacados del home."
                        >
                            <div className="mb-4 overflow-hidden rounded-[24px] border border-[#25A7CA]/20 bg-[linear-gradient(135deg,rgba(37,167,202,0.18),rgba(255,255,255,0.98))] shadow-[0_18px_45px_rgba(37,167,202,0.12)]">
                                <div className="flex flex-col gap-4 p-4">
                                    <div className="flex items-start gap-3">
                                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-[#117a98] shadow-sm">
                                            <Icon icon="solar:gallery-outline" width={22} />
                                        </div>
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#117a98]">
                                                Recomendación importante
                                            </p>
                                            <h3 className="mt-1 text-base font-semibold text-slate-900">
                                                {PRODUCT_BRAND_LOGO_GUIDE.title}
                                            </h3>
                                            <p className="mt-2 text-sm font-medium text-slate-700">
                                                {PRODUCT_BRAND_LOGO_GUIDE.detail}
                                            </p>
                                            <p className="mt-1 text-sm font-semibold text-slate-900">
                                                {PRODUCT_BRAND_LOGO_GUIDE.limit}
                                            </p>
                                            <p className="mt-2 text-sm text-slate-600">
                                                {PRODUCT_BRAND_LOGO_GUIDE.note}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="rounded-2xl border border-white/70 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                                        Tamaño recomendado
                                        <div className="mt-1 text-lg font-bold text-[#117a98]">
                                            77 x 44 px
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        brand_logo_media_file:
                                            event.target.files?.[0] ?? null,
                                    }))
                                }
                                className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                            />
                            {form.brand_logo_media_url ? (
                                <img
                                    src={form.brand_logo_media_url}
                                    alt={form.name || "Marca"}
                                    className="mt-3 h-24 w-full rounded-2xl border border-slate-200 bg-white object-contain p-4"
                                />
                            ) : null}
                        </Field>

                        <Field
                            label="Archivo técnico complementario"
                            hint="Opcional. La ficha técnica pública se genera igual desde el producto."
                        >
                            <input
                                type="file"
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        technical_sheet_file:
                                            event.target.files?.[0] ?? null,
                                    }))
                                }
                                className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                            />
                            {form.technical_sheet_url ? (
                                <a
                                    href={form.technical_sheet_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="mt-3 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700"
                                >
                                    <Icon icon="solar:file-text-outline" width={18} />
                                    Ver archivo actual
                                </a>
                            ) : null}
                        </Field>
                    </div>

                    <div className="space-y-3">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">
                                Galería
                            </p>
                            <p className="mt-1 text-xs text-slate-500">
                                Estas imágenes alimentan la galería pública del
                                producto.
                            </p>
                        </div>
                        <GalleryEditor
                            gallery={form.gallery}
                            onChange={(gallery) =>
                                setForm((current) => ({ ...current, gallery }))
                            }
                        />
                    </div>

                    <div className="space-y-3">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">
                                Productos relacionados
                            </p>
                            <p className="mt-1 text-xs text-slate-500">
                                Seleccioná los productos que querés mostrar al pie
                                de la ficha.
                            </p>
                        </div>

                        <div className="max-h-56 space-y-2 overflow-y-auto rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                            {products
                                .filter((item) => item.id !== form.id)
                                .map((item) => (
                                    <label
                                        key={item.id}
                                        className="flex items-center gap-3 rounded-2xl border border-transparent px-3 py-2 text-sm text-slate-700 transition hover:border-slate-200 hover:bg-white"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={form.related_product_ids.includes(
                                                item.id,
                                            )}
                                            onChange={(event) =>
                                                setForm((current) => ({
                                                    ...current,
                                                    related_product_ids: event.target
                                                        .checked
                                                        ? [
                                                              ...current.related_product_ids,
                                                              item.id,
                                                          ]
                                                        : current.related_product_ids.filter(
                                                              (currentId) =>
                                                                  currentId !== item.id,
                                                          ),
                                                }))
                                            }
                                        />
                                        <span>{item.name}</span>
                                    </label>
                                ))}
                        </div>
                    </div>

                    <div className="space-y-3">
                        <div>
                            <p className="text-sm font-semibold text-slate-900">
                                Tablas técnicas
                            </p>
                            <p className="mt-1 text-xs text-slate-500">
                                Estas tablas alimentan la ficha pública y el PDF
                                técnico del producto. Usá "Formato con péndulo" para tablas
                                como MS 132; si repetís exactamente "Fundido" o "Forjado"
                                en filas consecutivas, la web lo fusiona automáticamente.
                            </p>
                        </div>
                        <SpecTablesEditor
                            specTables={form.spec_tables}
                            onChange={(spec_tables) =>
                                setForm((current) => ({
                                    ...current,
                                    spec_tables,
                                }))
                            }
                        />
                    </div>

                    <div className="flex justify-end gap-3 border-t border-slate-200 pt-5">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={saving || loading || waitingForProduct}
                            className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                        >
                            {saving ? "Guardando..." : form.id ? "Guardar cambios" : "Crear producto"}
                        </button>
                    </div>
                </form>
            )}
        </ModalShell>
    );
}

export default function ProductsIndex({
    hero,
    families: initialFamilies,
    subfamilies: initialSubfamilies,
    products: initialProducts,
    stats,
    initialTab,
    publicProductsUrl,
    importTemplateUrl,
}) {
    const [tab, setTab] = useState(initialTab || "products");
    const [previewOpen, setPreviewOpen] = useState(false);
    const [coverForm, setCoverForm] = useState(emptyHeroForm(hero));
    const [coverSaving, setCoverSaving] = useState(false);
    const [families, setFamilies] = useState(initialFamilies ?? []);
    const [subfamilies, setSubfamilies] = useState(initialSubfamilies ?? []);
    const [products, setProducts] = useState(initialProducts ?? []);
    const [familyModalOpen, setFamilyModalOpen] = useState(false);
    const [editingFamily, setEditingFamily] = useState(null);
    const [subfamilyModalOpen, setSubfamilyModalOpen] = useState(false);
    const [editingSubfamily, setEditingSubfamily] = useState(null);
    const [productModalOpen, setProductModalOpen] = useState(false);
    const [editingProductId, setEditingProductId] = useState(null);
    const [importFile, setImportFile] = useState(null);
    const [importing, setImporting] = useState(false);

    useEffect(() => {
        setTab(initialTab || "products");
    }, [initialTab]);

    const visitTab = (nextTab) => {
        setTab(nextTab);
        router.visit(`/admin/products?tab=${nextTab}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const reloadPage = () => {
        router.reload({
            onSuccess: (page) => {
                setFamilies(page.props.families ?? []);
                setSubfamilies(page.props.subfamilies ?? []);
                setProducts(page.props.products ?? []);
                setCoverForm(emptyHeroForm(page.props.hero));
                setFamilyModalOpen(false);
                setEditingFamily(null);
                setSubfamilyModalOpen(false);
                setEditingSubfamily(null);
                setProductModalOpen(false);
                setEditingProductId(null);
                setImportFile(null);
            },
        });
    };

    const saveCover = async () => {
        setCoverSaving(true);

        try {
            let mediaId = coverForm.media_id;

            if (coverForm.image_file) {
                const uploaded = await uploadAsset(
                    coverForm.image_file,
                    coverForm.title || "Productos hero",
                );
                mediaId = uploaded.id;
            }

            await axios.put(`/admin/api/site-sections/${coverForm.id}`, {
                page_key: "productos",
                section_key: "hero",
                title: coverForm.title || null,
                media_id: mediaId,
                sort_order: coverForm.sort_order || "A",
                is_active: coverForm.is_active,
                sync_field_values: false,
                sync_items: false,
                field_values: [],
                items: [],
            });

            emitAdminToast("La portada de productos se actualizó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar la portada de productos.",
                "error",
            );
        } finally {
            setCoverSaving(false);
        }
    };

    const deleteFamily = async (item) => {
        if (!window.confirm(`¿Eliminar la familia "${item.name}"?`)) {
            return;
        }

        try {
            await axios.delete(`/admin/api/product-families/${item.id}`);
            emitAdminToast("La familia se eliminó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar la familia.",
                "error",
            );
        }
    };

    const deleteSubfamily = async (item) => {
        if (!window.confirm(`¿Eliminar la subfamilia "${item.name}"?`)) {
            return;
        }

        try {
            await axios.delete(`/admin/api/product-subfamilies/${item.id}`);
            emitAdminToast("La subfamilia se eliminó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar la subfamilia.",
                "error",
            );
        }
    };

    const deleteProduct = async (item) => {
        if (!window.confirm(`¿Eliminar el producto "${item.name}"?`)) {
            return;
        }

        try {
            await axios.delete(`/admin/api/products/${item.id}`);
            emitAdminToast("El producto se eliminó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar el producto.",
                "error",
            );
        }
    };

    const runImport = async () => {
        if (!importFile) {
            emitAdminToast("Seleccioná un archivo para importar.", "error");
            return;
        }

        setImporting(true);

        try {
            const payload = new FormData();
            payload.append("file", importFile);

            const response = await axios.post("/admin/api/product-import", payload, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            const summary = response.data.summary ?? {};
            emitAdminToast(
                `Importación completa. Familias: ${summary.families ?? 0}, subfamilias: ${summary.subfamilies ?? 0}, productos: ${summary.products ?? 0}.`,
            );
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo ejecutar la importación masiva.",
                "error",
            );
        } finally {
            setImporting(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Productos" />

            <PublicPreviewModal
                open={previewOpen}
                title="Vista pública de Productos"
                url={publicProductsUrl}
                onClose={() => setPreviewOpen(false)}
            />

            <FamilyModal
                open={familyModalOpen}
                families={families}
                family={editingFamily}
                onClose={() => {
                    setFamilyModalOpen(false);
                    setEditingFamily(null);
                }}
                onSaved={reloadPage}
            />

            <SubfamilyModal
                open={subfamilyModalOpen}
                subfamily={editingSubfamily}
                families={families}
                onClose={() => {
                    setSubfamilyModalOpen(false);
                    setEditingSubfamily(null);
                }}
                onSaved={reloadPage}
            />

            <ProductModal
                key={editingProductId ?? "new-product"}
                open={productModalOpen}
                productId={editingProductId}
                families={families}
                subfamilies={subfamilies}
                products={products}
                onClose={() => {
                    setProductModalOpen(false);
                    setEditingProductId(null);
                }}
                onSaved={reloadPage}
            />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:box-outline" width={14} />
                                    Productos / Catálogo público
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Gestión de productos
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Desde acá administrás la portada de productos,
                                    familias, subfamilias, fichas, galerías, tablas
                                    técnicas, relacionados e importación masiva del
                                    catálogo.
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

                                <a
                                    href={importTemplateUrl}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                >
                                    <Icon icon="solar:download-outline" width={18} />
                                    Descargar template
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    <StatCard
                        label="Familias"
                        value={stats?.families ?? families.length}
                        icon="solar:box-outline"
                    />
                    <StatCard
                        label="Subfamilias"
                        value={stats?.subfamilies ?? subfamilies.length}
                        icon="solar:widget-4-outline"
                    />
                    <StatCard
                        label="Productos"
                        value={stats?.products ?? products.length}
                        icon="solar:archive-outline"
                    />
                </section>

                <section className="flex flex-wrap gap-3">
                    <TabButton
                        active={tab === "cover"}
                        icon="solar:gallery-wide-outline"
                        label="Portada"
                        onClick={() => visitTab("cover")}
                    />
                    <TabButton
                        active={tab === "families"}
                        icon="solar:box-outline"
                        label="Familias"
                        onClick={() => visitTab("families")}
                    />
                    <TabButton
                        active={tab === "subfamilies"}
                        icon="solar:widget-4-outline"
                        label="Subfamilias"
                        onClick={() => visitTab("subfamilies")}
                    />
                    <TabButton
                        active={tab === "products"}
                        icon="solar:archive-outline"
                        label="Productos"
                        onClick={() => visitTab("products")}
                    />
                    <TabButton
                        active={tab === "import"}
                        icon="solar:upload-outline"
                        label="Importador masivo"
                        onClick={() => visitTab("import")}
                    />
                </section>

                {tab === "cover" ? (
                    <CoverEditor
                        form={coverForm}
                        onChange={(key, value) =>
                            setCoverForm((current) => ({
                                ...current,
                                [key]: value,
                            }))
                        }
                        onSave={saveCover}
                        saving={coverSaving}
                    />
                ) : null}

                {tab === "families" ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h2 className="text-2xl font-semibold text-slate-900">
                                    Familias
                                </h2>
                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    Administrá las tarjetas y familias visibles en la
                                    página pública de productos.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => {
                                    setEditingFamily(null);
                                    setFamilyModalOpen(true);
                                }}
                                className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                            >
                                <Icon icon="solar:add-circle-outline" width={18} />
                                Nueva familia
                            </button>
                        </div>

                        {families.length ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-slate-200 text-slate-500">
                                            <th className="px-4 py-3 font-semibold">Familia</th>
                                            <th className="px-4 py-3 font-semibold">Slug</th>
                                            <th className="px-4 py-3 font-semibold">Orden</th>
                                            <th className="px-4 py-3 font-semibold">Web</th>
                                            <th className="px-4 py-3 font-semibold">Home</th>
                                            <th className="px-4 py-3 font-semibold">Subfamilias</th>
                                            <th className="px-4 py-3 font-semibold">Productos</th>
                                            <th className="px-4 py-3 font-semibold text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {families.map((item) => (
                                            <tr key={item.id} className="border-b border-slate-100 align-top">
                                                <td className="px-4 py-4">
                                                    <div className="flex items-center gap-3">
                                                        {item.cover_media_url ? (
                                                            <img
                                                                src={item.cover_media_url}
                                                                alt={item.name}
                                                                className="h-12 w-12 rounded-2xl object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                                <Icon icon="solar:gallery-outline" width={18} />
                                                            </div>
                                                        )}
                                                        <div>
                                                            <p className="font-semibold text-slate-900">
                                                                {item.name}
                                                            </p>
                                                            <p className="text-xs text-slate-500">
                                                                {item.is_active ? "Activa" : "Oculta"}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">{item.slug}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.sort_order}</td>
                                                <td className="px-4 py-4 text-slate-600">
                                                    {item.show_on_products_page ? "Sí" : "No"}
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">
                                                    {item.show_on_home ? "Sí" : "No"}
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">{item.subfamilies_count}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.products_count}</td>
                                                <td className="px-4 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setEditingFamily(item);
                                                                setFamilyModalOpen(true);
                                                            }}
                                                            className="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => deleteFamily(item)}
                                                            className="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <EmptyState
                                title="Todavía no hay familias"
                                description="Creá la primera familia de productos para empezar a alimentar la web pública."
                            />
                        )}
                    </section>
                ) : null}

                {tab === "subfamilies" ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h2 className="text-2xl font-semibold text-slate-900">
                                    Subfamilias
                                </h2>
                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    Organizá las subcategorías que se muestran dentro
                                    de cada familia.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => {
                                    setEditingSubfamily(null);
                                    setSubfamilyModalOpen(true);
                                }}
                                className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                            >
                                <Icon icon="solar:add-circle-outline" width={18} />
                                Nueva subfamilia
                            </button>
                        </div>

                        {subfamilies.length ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-slate-200 text-slate-500">
                                            <th className="px-4 py-3 font-semibold">Subfamilia</th>
                                            <th className="px-4 py-3 font-semibold">Familia</th>
                                            <th className="px-4 py-3 font-semibold">Slug</th>
                                            <th className="px-4 py-3 font-semibold">Orden</th>
                                            <th className="px-4 py-3 font-semibold">Home</th>
                                            <th className="px-4 py-3 font-semibold">Página familia</th>
                                            <th className="px-4 py-3 font-semibold">Productos</th>
                                            <th className="px-4 py-3 font-semibold text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {subfamilies.map((item) => (
                                            <tr key={item.id} className="border-b border-slate-100 align-top">
                                                <td className="px-4 py-4">
                                                    <div>
                                                        <p className="font-semibold text-slate-900">
                                                            {item.name}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {item.is_active ? "Activa" : "Oculta"}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">{item.family_name}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.slug}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.sort_order}</td>
                                                <td className="px-4 py-4 text-slate-600">
                                                    {item.show_on_home ? "Sí" : "No"}
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">
                                                    {item.show_on_family_page ? "Sí" : "No"}
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">{item.products_count}</td>
                                                <td className="px-4 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setEditingSubfamily(item);
                                                                setSubfamilyModalOpen(true);
                                                            }}
                                                            className="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => deleteSubfamily(item)}
                                                            className="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <EmptyState
                                title="Todavía no hay subfamilias"
                                description="Creá subfamilias para organizar mejor el catálogo público."
                            />
                        )}
                    </section>
                ) : null}

                {tab === "products" ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h2 className="text-2xl font-semibold text-slate-900">
                                    Productos
                                </h2>
                                <p className="mt-2 text-sm leading-6 text-slate-500">
                                    Cargá fichas completas con galería, datos
                                    técnicos, relacionados y visibilidad en la web
                                    pública.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => {
                                    setEditingProductId(null);
                                    setProductModalOpen(true);
                                }}
                                className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                            >
                                <Icon icon="solar:add-circle-outline" width={18} />
                                Nuevo producto
                            </button>
                        </div>

                        {products.length ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-slate-200 text-slate-500">
                                            <th className="px-4 py-3 font-semibold">Producto</th>
                                            <th className="px-4 py-3 font-semibold">Familia</th>
                                            <th className="px-4 py-3 font-semibold">Subfamilia</th>
                                            <th className="px-4 py-3 font-semibold">SKU</th>
                                            <th className="px-4 py-3 font-semibold">Galería</th>
                                            <th className="px-4 py-3 font-semibold">Tablas</th>
                                            <th className="px-4 py-3 font-semibold">Relacionados</th>
                                            <th className="px-4 py-3 font-semibold text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {products.map((item) => (
                                            <tr key={item.id} className="border-b border-slate-100 align-top">
                                                <td className="px-4 py-4">
                                                    <div className="flex items-center gap-3">
                                                        {item.main_media_url ? (
                                                            <img
                                                                src={item.main_media_url}
                                                                alt={item.name}
                                                                className="h-12 w-12 rounded-2xl object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                                <Icon icon="solar:archive-outline" width={18} />
                                                            </div>
                                                        )}
                                                        <div>
                                                            <p className="font-semibold text-slate-900">
                                                                {item.name}
                                                            </p>
                                                            <p className="text-xs text-slate-500">
                                                                {item.is_active ? "Activo" : "Oculto"}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-4 text-slate-600">{item.family_name}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.subfamily_name || "-"}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.sku || "-"}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.gallery_count}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.spec_tables_count}</td>
                                                <td className="px-4 py-4 text-slate-600">{item.related_count}</td>
                                                <td className="px-4 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setEditingProductId(item.id);
                                                                setProductModalOpen(true);
                                                            }}
                                                            className="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => deleteProduct(item)}
                                                            className="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <EmptyState
                                title="Todavía no hay productos"
                                description="Creá el primer producto o usá el importador masivo para cargar el catálogo."
                            />
                        )}
                    </section>
                ) : null}

                {tab === "import" ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                            <div className="space-y-5">
                                <div>
                                    <h2 className="text-2xl font-semibold text-slate-900">
                                        Importador masivo
                                    </h2>
                                    <p className="mt-2 text-sm leading-6 text-slate-500">
                                        Importá familias, subfamilias y productos en
                                        lote usando el template Excel del panel.
                                    </p>
                                </div>

                                <Field
                                    label="Archivo Excel o CSV"
                                    hint="Para importar todo completo usá el template XLSX con hojas families, subfamilies y products."
                                >
                                    <input
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        onChange={(event) =>
                                            setImportFile(event.target.files?.[0] ?? null)
                                        }
                                        className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                    />
                                </Field>

                                <div className="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onClick={runImport}
                                        disabled={importing}
                                        className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                    >
                                        <Icon icon="solar:upload-outline" width={18} />
                                        {importing ? "Importando..." : "Ejecutar importación"}
                                    </button>

                                    <a
                                        href={importTemplateUrl}
                                        className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                    >
                                        <Icon icon="solar:download-outline" width={18} />
                                        Bajar template
                                    </a>
                                </div>
                            </div>

                            <aside className="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    Estructura
                                </p>
                                <div className="mt-4 space-y-4 text-sm leading-6 text-slate-600">
                                    <p>
                                        `families`: nombre, slug, descripción,
                                        color, orden, visibilidad y media IDs.
                                    </p>
                                    <p>
                                        `subfamilies`: familia padre por slug,
                                        nombre, slug, descripciones, portada y
                                        visibilidad.
                                    </p>
                                    <p>
                                        `products`: familia, subfamilia, ficha
                                        completa, galerías, relacionados y
                                        `spec_tables_json`.
                                    </p>
                                    <p>
                                        `gallery_media_ids` va separado por comas.
                                    </p>
                                    <p>
                                        `related_product_slugs` va separado por
                                        comas.
                                    </p>
                                    <p>
                                        `spec_tables_json` acepta un array JSON con
                                        tablas, columnas y filas.
                                    </p>
                                </div>
                            </aside>
                        </div>
                    </section>
                ) : null}
            </div>
        </AdminLayout>
    );
}
