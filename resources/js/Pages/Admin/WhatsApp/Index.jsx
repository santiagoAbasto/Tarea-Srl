import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useEffect, useState } from "react";

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

export default function WhatsAppIndex({ whatsappUrl: initialUrl }) {
    const [url, setUrl] = useState(initialUrl ?? "");
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setUrl(initialUrl ?? "");
    }, [initialUrl]);

    const save = async () => {
        setSaving(true);

        try {
            await axios.put("/admin/api/footer-settings", {
                whatsapp_url: url || null,
            });

            emitAdminToast("El link de WhatsApp se actualizó correctamente.");
            router.reload();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo guardar el link de WhatsApp.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="WhatsApp flotante" />

            <div className="space-y-6">
                {/* Header */}
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="max-w-3xl">
                            <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                <Icon icon="solar:phone-calling-rounded-outline" width={14} />
                                Extras / WhatsApp flotante
                            </div>
                            <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                WhatsApp flotante
                            </h1>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                Configurá el link del botón de WhatsApp que aparece
                                flotando en todas las páginas de la web.
                            </p>
                        </div>
                    </div>
                </section>

                {/* Editor */}
                <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-6">
                        <h2 className="text-2xl font-semibold text-slate-900">
                            Link de WhatsApp
                        </h2>
                        <p className="mt-2 text-sm text-slate-500">
                            Usá el formato{" "}
                            <code className="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-mono text-slate-700">
                                https://wa.me/549XXXXXXXXXX
                            </code>{" "}
                            incluyendo el código de país sin el signo +.
                        </p>
                    </div>

                    <div className="max-w-xl space-y-5">
                        <Field
                            label="URL de WhatsApp"
                            hint="Ejemplo: https://wa.me/5491112345678"
                        >
                            <div className="relative">
                                <span className="absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <Icon icon="solar:phone-calling-rounded-outline" width={18} />
                                </span>
                                <input
                                    type="text"
                                    value={url}
                                    onChange={(e) => setUrl(e.target.value)}
                                    placeholder="https://wa.me/5491112345678"
                                    className="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </div>
                        </Field>

                        {url ? (
                            <a
                                href={url}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                            >
                                <Icon icon="solar:square-arrow-right-up-outline" width={16} />
                                Probar link
                            </a>
                        ) : null}

                        <div className="flex justify-end border-t border-slate-200 pt-5">
                            <button
                                type="button"
                                onClick={save}
                                disabled={saving}
                                className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                            >
                                {saving ? "Guardando..." : "Guardar"}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
