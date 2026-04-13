import PublicPreviewModal from "@/Components/Admin/PublicPreviewModal";
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

function buildYouTubeEmbed(url) {
    const clean = (url || "").trim();

    if (!clean) return "";

    if (
        clean.includes("youtube.com/embed/") ||
        clean.includes("youtube-nocookie.com/embed/")
    ) {
        return clean;
    }

    const match = clean.match(
        /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/]+)/i,
    );

    if (!match) return "";

    return `https://www.youtube-nocookie.com/embed/${match[1]}?rel=0&modestbranding=1`;
}

function getEmptyForm() {
    return {
        id: null,
        title: "",
        description: "",
        button_text: "",
        button_url: "",
        media_type: "image",
        alt_text: "",
        sort_order: "",
        autoplay_override_seconds: "",
        is_active: true,
        youtube_url: "",
        desktop_file: null,
    };
}

function formatSlide(slide) {
    return {
        id: slide.id,
        title: slide.title ?? "",
        description: slide.description ?? "",
        button_text: slide.button_text ?? "",
        button_url: slide.button_url ?? "",
        media_type: slide.media_type ?? "image",
        alt_text: slide.alt_text ?? "",
        sort_order: slide.sort_order ?? "",
        autoplay_override_seconds: slide.autoplay_override_seconds ?? "",
        is_active: !!slide.is_active,
        youtube_url:
            slide.media_type === "youtube"
                ? slide.desktop_media_path ?? ""
                : "",
        desktop_file: null,
        desktop_media_id: slide.desktop_media_id ?? null,
        desktop_media_url: slide.desktop_media_url ?? "",
        desktop_youtube_embed_url: slide.desktop_youtube_embed_url ?? "",
        updated_at: slide.updated_at ?? null,
    };
}

function getSlidePreviewMedia(form, existingSlide, filePreviewUrl) {
    if (form.media_type === "youtube") {
        return {
            type: "youtube",
            url:
                buildYouTubeEmbed(form.youtube_url) ||
                existingSlide?.desktop_youtube_embed_url ||
                "",
        };
    }

    if (filePreviewUrl) {
        return {
            type: form.media_type,
            url: filePreviewUrl,
        };
    }

    return {
        type: form.media_type,
        url: existingSlide?.desktop_media_url || "",
    };
}

function SlidePreview({ slide, previewMedia }) {
    return (
        <div className="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div className="relative min-h-[520px] bg-[linear-gradient(180deg,rgba(7,18,27,.42),rgba(7,18,27,.42))]">
                <div className="absolute inset-0">
                    {previewMedia.type === "youtube" && previewMedia.url ? (
                        <iframe
                            src={previewMedia.url}
                            title={slide.alt_text || slide.title || "Preview del slide"}
                            className="h-full w-full object-cover"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    ) : null}

                    {previewMedia.type === "video" && previewMedia.url ? (
                        <video
                            src={previewMedia.url}
                            className="h-full w-full object-cover"
                            autoPlay
                            muted
                            loop
                            playsInline
                            controls={false}
                        />
                    ) : null}

                    {previewMedia.type === "image" && previewMedia.url ? (
                        <img
                            src={previewMedia.url}
                            alt={slide.alt_text || slide.title || "Preview del slide"}
                            className="h-full w-full object-cover"
                        />
                    ) : null}

                    {!previewMedia.url ? (
                        <div className="flex h-full items-center justify-center bg-[radial-gradient(circle_at_top_left,rgba(37,167,202,0.22),transparent_38%),linear-gradient(135deg,#0f172a_0%,#111827_55%,#1f2937_100%)] text-slate-200">
                            <div className="max-w-sm px-8 text-center">
                                <p className="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-200">
                                    Preview
                                </p>
                                <p className="mt-4 text-sm leading-6 text-slate-300">
                                    Cargá una imagen, video o URL de YouTube para ver
                                    el resultado del hero.
                                </p>
                            </div>
                        </div>
                    ) : null}
                </div>

                <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.71)_0%,rgba(0,0,0,0)_100%),linear-gradient(180deg,rgba(0,0,0,0.43)_0%,rgba(0,0,0,0)_100%),rgba(0,0,0,0.30)]" />

                <div className="relative z-[2] flex min-h-[520px] items-start px-8 pb-10 pt-32 md:px-12 md:pt-44">
                    <div className="w-full max-w-[510px]">
                        <h2 className="whitespace-pre-line font-['Montserrat'] text-[32px] font-semibold leading-[1.2] text-white md:text-[48px]">
                            {slide.title || "INNOVACION Y CRECIMIENTO"}
                        </h2>
                        <p className="mt-3 max-w-[491px] font-['Raleway'] text-lg leading-[1.2] text-white md:text-[20px]">
                            {slide.description ||
                                "Diseñamos sistemas, procesos y productos para generar soluciones innovadoras que respondan a las necesidades de nuestros clientes."}
                        </p>
                        {slide.button_text ? (
                            <span className="mt-8 inline-flex h-[41px] min-w-[232px] items-center justify-center border border-white px-5 text-center font-['Montserrat'] text-sm font-semibold uppercase tracking-[0.02em] text-white md:text-base">
                                {slide.button_text}
                            </span>
                        ) : null}

                        <div className="mt-16 flex gap-3 md:mt-24">
                            <span className="h-[6px] w-[43px] bg-white" />
                            <span className="h-[6px] w-[43px] bg-white/30" />
                            <span className="h-[6px] w-[43px] bg-white/30" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function HomeHeroSlidesIndex({ slides: initialSlides, publicHomeUrl }) {
    const [slides, setSlides] = useState(initialSlides ?? []);
    const [selectedId, setSelectedId] = useState(initialSlides?.[0]?.id ?? null);
    const [form, setForm] = useState(
        initialSlides?.[0] ? formatSlide(initialSlides[0]) : getEmptyForm(),
    );
    const [filePreviewUrl, setFilePreviewUrl] = useState("");
    const [saving, setSaving] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [errors, setErrors] = useState({});
    const [previewOpen, setPreviewOpen] = useState(false);

    const selectedSlide =
        slides.find((slide) => slide.id === selectedId) ?? null;
    const previewMedia = getSlidePreviewMedia(form, selectedSlide, filePreviewUrl);

    useEffect(() => {
        if (!form.desktop_file) {
            setFilePreviewUrl("");
            return undefined;
        }

        const url = URL.createObjectURL(form.desktop_file);
        setFilePreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [form.desktop_file]);

    const selectSlide = (slide) => {
        setSelectedId(slide.id);
        setForm(formatSlide(slide));
        setErrors({});
    };

    const startNewSlide = () => {
        setSelectedId(null);
        setForm(getEmptyForm());
        setErrors({});
    };

    const updateForm = (key, value) => {
        setForm((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const uploadMediaAsset = async () => {
        if (form.media_type === "youtube") {
            if (!form.youtube_url.trim()) {
                throw new Error("Ingresá una URL de YouTube para el slide.");
            }

            const youtubeEmbed = buildYouTubeEmbed(form.youtube_url);

            if (!youtubeEmbed) {
                throw new Error("La URL de YouTube no es válida.");
            }

            const response = await axios.post("/admin/api/media-assets", {
                type: "youtube",
                path: form.youtube_url.trim(),
                title: form.title || "Slide Home",
                alt_text: form.alt_text || null,
                meta_json: {
                    youtube_embed: youtubeEmbed,
                },
            });

            return response.data;
        }

        const canReuseExistingMedia =
            !!form.desktop_media_id &&
            !!selectedSlide &&
            selectedSlide.media_type === form.media_type;

        if (!form.desktop_file && !canReuseExistingMedia) {
            throw new Error("Cargá una imagen o video para el slide.");
        }

        if (!form.desktop_file) {
            return { id: form.desktop_media_id };
        }

        const payload = new FormData();
        payload.append("file", form.desktop_file);
        payload.append("title", form.title || "Slide Home");
        payload.append("alt_text", form.alt_text || "");

        const response = await axios.post("/admin/api/media-assets", payload, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        return response.data;
    };

    const handleSave = async (event) => {
        event.preventDefault();
        setSaving(true);
        setErrors({});

        try {
            const mediaAsset = await uploadMediaAsset();

            const payload = {
                title: form.title || null,
                subtitle: null,
                description: form.description || null,
                button_text: form.button_text || null,
                button_url: form.button_url || null,
                media_type: form.media_type,
                desktop_media_id: mediaAsset?.id ?? form.desktop_media_id,
                mobile_media_id: mediaAsset?.id ?? form.desktop_media_id,
                alt_text: form.alt_text || null,
                sort_order: form.sort_order || null,
                autoplay_override_seconds:
                    form.autoplay_override_seconds === "" ||
                    form.autoplay_override_seconds === null
                        ? null
                        : Number(form.autoplay_override_seconds),
                is_active: form.is_active,
            };

            const response = form.id
                ? await axios.put(`/admin/api/home-hero-slides/${form.id}`, payload)
                : await axios.post("/admin/api/home-hero-slides", payload);

            const refresh = await axios.get("/admin/api/home-hero-slides", {
                params: {
                    per_page: 100,
                },
            });

            const nextSlides = refresh.data?.data ?? [];

            setSlides(nextSlides);

            const savedSlideId = response.data.id;
            const savedSlide =
                nextSlides.find((slide) => slide.id === savedSlideId) ?? null;

            if (savedSlide) {
                setSelectedId(savedSlide.id);
                setForm(formatSlide(savedSlide));
            }

            emitAdminToast(
                form.id
                    ? "El slide se actualizó correctamente."
                    : "El slide se creó correctamente.",
            );
        } catch (error) {
            if (error?.response?.status === 422) {
                setErrors(error.response.data.errors ?? {});
            }

            emitAdminToast(
                error?.response?.data?.message ||
                    error?.message ||
                    "No se pudo guardar el slide.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async () => {
        if (!form.id || deleting) return;

        const confirmed = window.confirm(
            "¿Querés eliminar este slide del hero principal?",
        );

        if (!confirmed) return;

        setDeleting(true);

        try {
            await axios.delete(`/admin/api/home-hero-slides/${form.id}`);

            const nextSlides = slides.filter((slide) => slide.id !== form.id);
            setSlides(nextSlides);

            if (nextSlides[0]) {
                selectSlide(nextSlides[0]);
            } else {
                startNewSlide();
            }

            emitAdminToast("El slide se eliminó correctamente.");
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar el slide.",
                "error",
            );
        } finally {
            setDeleting(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Home Sliders" />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.18),_transparent_38%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_45%,#eff6ff_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:gallery-wide-outline" width={14} />
                                    Home / Sliders
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Hero principal de la web
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Acá administramos exactamente el slider que se
                                    muestra en la home pública. Podés crear nuevos
                                    slides con imagen, video local o video embebido
                                    de YouTube y mantener el mismo look del sitio.
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    onClick={() => setPreviewOpen(true)}
                                    className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                                >
                                    <Icon icon="solar:square-arrow-right-up-outline" width={18} />
                                    Ver home pública
                                </button>
                                <button
                                    type="button"
                                    onClick={startNewSlide}
                                    className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                                >
                                    <Icon icon="solar:add-circle-outline" width={18} />
                                    Nuevo slider
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-6 xl:grid-cols-[360px,minmax(0,1fr)]">
                    <div className="space-y-4">
                        <div className="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 className="text-lg font-semibold text-slate-900">
                                        Sliders actuales
                                    </h2>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Tomamos lo que hoy ya existe en la home.
                                    </p>
                                </div>
                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    {slides.length} total
                                </span>
                            </div>

                            <div className="space-y-3">
                                {slides.map((slide) => {
                                    const active = slide.id === selectedId;
                                    const mediaLabel =
                                        slide.media_type === "youtube"
                                            ? "YouTube"
                                            : slide.media_type === "video"
                                              ? "Video"
                                              : "Imagen";

                                    return (
                                        <button
                                            key={slide.id}
                                            type="button"
                                            onClick={() => selectSlide(slide)}
                                            className={`w-full rounded-[24px] border p-3 text-left transition ${
                                                active
                                                    ? "border-[#25A7CA]/30 bg-[#25A7CA]/8 shadow-sm"
                                                    : "border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50"
                                            }`}
                                        >
                                            <div className="flex items-start gap-3">
                                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                                    <Icon
                                                        icon={
                                                            slide.media_type ===
                                                            "youtube"
                                                                ? "solar:videocamera-record-outline"
                                                                : slide.media_type ===
                                                                    "video"
                                                                  ? "solar:playback-speed-outline"
                                                                  : "solar:gallery-outline"
                                                        }
                                                        width={18}
                                                    />
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-center gap-2">
                                                        <p className="truncate text-sm font-semibold text-slate-900">
                                                            {slide.title ||
                                                                "Slide sin título"}
                                                        </p>
                                                        {!slide.is_active ? (
                                                            <span className="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                                                Inactivo
                                                            </span>
                                                        ) : null}
                                                    </div>
                                                    <p className="mt-1 text-xs uppercase tracking-[0.16em] text-slate-400">
                                                        {mediaLabel} · Orden{" "}
                                                        {slide.sort_order || "-"}
                                                    </p>
                                                    <p className="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">
                                                        {slide.description ||
                                                            "Sin descripción aún."}
                                                    </p>
                                                </div>
                                            </div>
                                        </button>
                                    );
                                })}

                                {slides.length === 0 ? (
                                    <div className="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                        Todavía no hay slides cargados.
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <SlidePreview slide={form} previewMedia={previewMedia} />

                        <form
                            onSubmit={handleSave}
                            className="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <div className="flex flex-col gap-5 border-b border-slate-200 pb-5 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h2 className="text-xl font-semibold text-slate-900">
                                        {form.id
                                            ? "Editar slider"
                                            : "Crear nuevo slider"}
                                    </h2>
                                    <p className="mt-2 max-w-2xl text-sm text-slate-500">
                                        El preview respeta el estilo del hero
                                        público actual para que el resultado sea
                                        fiel antes de guardar.
                                    </p>
                                </div>

                                {form.id ? (
                                    <button
                                        type="button"
                                        onClick={handleDelete}
                                        disabled={deleting}
                                        className="inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <Icon icon="solar:trash-bin-trash-outline" width={18} />
                                        {deleting ? "Eliminando..." : "Eliminar"}
                                    </button>
                                ) : null}
                            </div>

                            <div className="mt-6 grid gap-5 md:grid-cols-2">
                                <div className="md:col-span-2">
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Título principal
                                    </label>
                                    <textarea
                                        value={form.title}
                                        onChange={(event) =>
                                            updateForm("title", event.target.value)
                                        }
                                        rows={3}
                                        className="min-h-[108px] w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="INNOVACIÓN Y CRECIMIENTO"
                                    />
                                    {errors.title ? (
                                        <p className="mt-2 text-sm text-red-600">
                                            {errors.title}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="md:col-span-2">
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Descripción
                                    </label>
                                    <textarea
                                        value={form.description}
                                        onChange={(event) =>
                                            updateForm(
                                                "description",
                                                event.target.value,
                                            )
                                        }
                                        rows={4}
                                        className="min-h-[132px] w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="Diseñamos sistemas, procesos y productos..."
                                    />
                                    {errors.description ? (
                                        <p className="mt-2 text-sm text-red-600">
                                            {errors.description}
                                        </p>
                                    ) : null}
                                </div>

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
                                        placeholder="Más información"
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

                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Tipo de slide
                                    </label>
                                    <select
                                        value={form.media_type}
                                        onChange={(event) =>
                                            updateForm(
                                                "media_type",
                                                event.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                    >
                                        <option value="image">Imagen</option>
                                        <option value="video">Video local</option>
                                        <option value="youtube">
                                            Video embebido de YouTube
                                        </option>
                                    </select>
                                </div>

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
                                        placeholder="A"
                                    />
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Texto alternativo
                                    </label>
                                    <input
                                        type="text"
                                        value={form.alt_text}
                                        onChange={(event) =>
                                            updateForm("alt_text", event.target.value)
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="Video principal de Tarea SRL"
                                    />
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-800">
                                        Autoplay en segundos
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        value={form.autoplay_override_seconds}
                                        onChange={(event) =>
                                            updateForm(
                                                "autoplay_override_seconds",
                                                event.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                        placeholder="6"
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <div className="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                                        <label className="mb-2 block text-sm font-semibold text-slate-800">
                                            Archivo o enlace principal
                                        </label>

                                        {form.media_type === "youtube" ? (
                                            <input
                                                type="text"
                                                value={form.youtube_url}
                                                onChange={(event) =>
                                                    updateForm(
                                                        "youtube_url",
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                                placeholder="https://www.youtube.com/watch?v=..."
                                            />
                                        ) : (
                                            <input
                                                type="file"
                                                accept={
                                                    form.media_type === "video"
                                                        ? "video/*"
                                                        : "image/*"
                                                }
                                                onChange={(event) =>
                                                    updateForm(
                                                        "desktop_file",
                                                        event.target.files?.[0] ??
                                                            null,
                                                    )
                                                }
                                                className="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#25A7CA]/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#117a98] hover:file:bg-[#25A7CA]/15"
                                            />
                                        )}

                                        <div className="mt-3 grid gap-3 text-sm text-slate-500 md:grid-cols-2">
                                            <p>
                                                El archivo que cargues acá se usa
                                                para desktop y mobile, igual que
                                                en la home pública actual.
                                            </p>
                                            <p>
                                                Formatos admitidos: imagen local,
                                                video local o URL embebida de
                                                YouTube.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="md:col-span-2">
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
                                        Slide activo en la home
                                    </label>
                                </div>
                            </div>

                            <div className="mt-6 flex flex-wrap items-center gap-3">
                                <button
                                    type="submit"
                                    disabled={saving}
                                    className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    <Icon icon="solar:diskette-outline" width={18} />
                                    {saving
                                        ? "Guardando..."
                                        : form.id
                                          ? "Guardar cambios"
                                          : "Crear slider"}
                                </button>

                                <button
                                    type="button"
                                    onClick={startNewSlide}
                                    className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                >
                                    <Icon icon="solar:restart-outline" width={18} />
                                    Nuevo limpio
                                </button>
                            </div>
                        </form>
                    </div>
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
