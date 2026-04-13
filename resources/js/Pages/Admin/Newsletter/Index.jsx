import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useState, useEffect } from "react";

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

function StatusBadge({ active }) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${
                active
                    ? "bg-emerald-100 text-emerald-700"
                    : "bg-slate-100 text-slate-500"
            }`}
        >
            {active ? "Activo" : "Inactivo"}
        </span>
    );
}

export default function NewsletterIndex({
    subscribers: initialSubscribers,
    stats,
}) {
    const [subscribers, setSubscribers] = useState(initialSubscribers ?? []);
    const [showCampaign, setShowCampaign] = useState(false);
    const [subject, setSubject] = useState("");
    const [body, setBody] = useState("");
    const [sending, setSending] = useState(false);
    const [busyId, setBusyId] = useState(null);

    useEffect(() => {
        setSubscribers(initialSubscribers ?? []);
    }, [initialSubscribers]);

    const reloadPage = () => {
        router.reload({
            onSuccess: (page) => {
                setSubscribers(page.props.subscribers ?? []);
            },
        });
    };

    const sendCampaign = async () => {
        if (!subject.trim() || !body.trim()) {
            emitAdminToast("Completá el asunto y el cuerpo del mensaje.", "error");
            return;
        }

        setSending(true);

        try {
            const response = await axios.post("/admin/api/newsletter/send", {
                subject,
                body,
            });

            emitAdminToast(
                response.data?.message ?? "Campaña enviada correctamente.",
            );
            setSubject("");
            setBody("");
            setShowCampaign(false);
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message || "No se pudo enviar la campaña.",
                "error",
            );
        } finally {
            setSending(false);
        }
    };

    const toggleActive = async (item) => {
        setBusyId(item.id);

        try {
            await axios.put(`/admin/api/newsletter-subscribers/${item.id}`, {
                email: item.email,
                is_active: !item.is_active,
            });

            emitAdminToast(
                item.is_active
                    ? "Suscriptor dado de baja."
                    : "Suscriptor reactivado.",
            );
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message || "No se pudo actualizar el suscriptor.",
                "error",
            );
        } finally {
            setBusyId(null);
        }
    };

    const deleteSubscriber = async (item) => {
        if (!window.confirm(`¿Eliminar el suscriptor "${item.email}"?`)) return;

        setBusyId(item.id);

        try {
            await axios.delete(`/admin/api/newsletter-subscribers/${item.id}`);
            emitAdminToast("Suscriptor eliminado.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message || "No se pudo eliminar el suscriptor.",
                "error",
            );
        } finally {
            setBusyId(null);
        }
    };

    const activeCount = stats?.active ?? subscribers.filter((s) => s.is_active).length;

    return (
        <AdminLayout>
            <Head title="Newsletter" />

            <div className="space-y-6">
                {/* Header */}
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:letter-outline" width={14} />
                                    Extras / Newsletter
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Newsletter
                                </h1>
                                <p className="mt-3 text-sm leading-6 text-slate-600">
                                    {stats?.total ?? subscribers.length} suscriptores · {activeCount} activos
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setShowCampaign((v) => !v)}
                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25A7CA] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8]"
                            >
                                <Icon icon="solar:letter-outline" width={18} />
                                Enviar Email
                            </button>
                        </div>
                    </div>
                </section>

                {/* Stats */}
                <section className="grid gap-4 md:grid-cols-2">
                    <StatCard
                        label="Suscriptores totales"
                        value={stats?.total ?? subscribers.length}
                        icon="solar:users-group-rounded-outline"
                    />
                    <StatCard
                        label="Suscriptores activos"
                        value={activeCount}
                        icon="solar:letter-outline"
                    />
                </section>

                {/* Campaign form */}
                {showCampaign ? (
                    <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <div className="flex items-center gap-2">
                                    <Icon icon="solar:letter-outline" className="text-[#25A7CA]" width={20} />
                                    <h2 className="text-lg font-semibold text-slate-900">
                                        Enviar campaña
                                    </h2>
                                </div>
                                <p className="mt-1 text-sm text-slate-500">
                                    Se enviará a{" "}
                                    <strong className="text-slate-700">{activeCount} suscriptor{activeCount !== 1 ? "es" : ""} activo{activeCount !== 1 ? "s" : ""}</strong>{" "}
                                    via Mailtrap
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowCampaign(false)}
                                className="flex h-9 w-9 items-center justify-center rounded-2xl border border-slate-200 text-slate-400 transition hover:border-slate-300 hover:text-slate-700"
                            >
                                <Icon icon="solar:close-circle-outline" width={18} />
                            </button>
                        </div>

                        <div className="space-y-4">
                            <label className="block space-y-2">
                                <p className="text-sm font-semibold text-slate-900">
                                    Asunto <span className="text-rose-500">*</span>
                                </p>
                                <input
                                    type="text"
                                    value={subject}
                                    onChange={(e) => setSubject(e.target.value)}
                                    placeholder="Ej: Novedades de abril 2026"
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </label>

                            <label className="block space-y-2">
                                <p className="text-sm font-semibold text-slate-900">
                                    Cuerpo del mensaje <span className="text-rose-500">*</span>
                                </p>
                                <textarea
                                    value={body}
                                    onChange={(e) => setBody(e.target.value)}
                                    rows={6}
                                    placeholder="Escribí el contenido del email aquí..."
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </label>

                            <div className="flex items-center justify-between border-t border-slate-200 pt-4">
                                <p className="flex items-center gap-2 text-xs text-slate-500">
                                    <Icon icon="solar:check-circle-outline" className="text-emerald-500" width={16} />
                                    Enviando por el servidor de correo configurado
                                </p>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setShowCampaign(false)}
                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="button"
                                        onClick={sendCampaign}
                                        disabled={sending}
                                        className="inline-flex items-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                    >
                                        <Icon icon="solar:plain-2-outline" width={16} />
                                        {sending
                                            ? "Enviando..."
                                            : `Enviar a ${activeCount} suscriptor${activeCount !== 1 ? "es" : ""}`}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                ) : null}

                {/* Subscribers table */}
                <section className="rounded-[30px] border border-slate-200 bg-white shadow-sm">
                    {subscribers.length ? (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-slate-200">
                                        <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            #
                                        </th>
                                        <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            Email
                                        </th>
                                        <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            Estado
                                        </th>
                                        <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            Fecha suscripción
                                        </th>
                                        <th className="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {subscribers.map((item, index) => (
                                        <tr key={item.id} className="transition hover:bg-slate-50">
                                            <td className="px-6 py-4 text-slate-400">
                                                {index + 1}
                                            </td>
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                {item.email}
                                            </td>
                                            <td className="px-6 py-4">
                                                <StatusBadge active={item.is_active} />
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {item.subscribed_at ?? "-"}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        title={item.is_active ? "Dar de baja" : "Reactivar"}
                                                        onClick={() => toggleActive(item)}
                                                        disabled={busyId === item.id}
                                                        className={`flex h-9 w-9 items-center justify-center rounded-2xl border transition disabled:opacity-60 ${
                                                            item.is_active
                                                                ? "border-amber-200 bg-white text-amber-500 hover:bg-amber-50"
                                                                : "border-emerald-200 bg-white text-emerald-500 hover:bg-emerald-50"
                                                        }`}
                                                    >
                                                        <Icon
                                                            icon={item.is_active ? "solar:user-cross-outline" : "solar:user-check-outline"}
                                                            width={16}
                                                        />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        title="Eliminar"
                                                        onClick={() => deleteSubscriber(item)}
                                                        disabled={busyId === item.id}
                                                        className="flex h-9 w-9 items-center justify-center rounded-2xl border border-rose-200 bg-white text-rose-500 transition hover:bg-rose-50 disabled:opacity-60"
                                                    >
                                                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="px-6 py-12 text-center">
                            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <Icon icon="solar:letter-outline" width={24} />
                            </div>
                            <h3 className="mt-4 text-lg font-semibold text-slate-900">
                                Todavía no hay suscriptores
                            </h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Cuando alguien se suscriba desde la web, aparecerá acá.
                            </p>
                        </div>
                    )}
                </section>
            </div>
        </AdminLayout>
    );
}
