import Sidebar from "@/Components/Admin/Sidebar";
import Header from "@/Components/Admin/Header";
import { usePage } from "@inertiajs/react";
import Toast from "@/Components/Admin/Toast";
import { useState } from "react";

export default function AdminLayout({ children }) {
    const { flash } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="flex h-screen bg-[#F3F4F6] font-sans text-[#111827]">
            <Toast flash={flash} />

            <div className="hidden shrink-0 border-r border-[#E5E7EB] bg-white md:block">
                <Sidebar />
            </div>

            {sidebarOpen && (
                <div className="fixed inset-0 z-40 md:hidden">
                    <button
                        type="button"
                        aria-label="Cerrar menú"
                        onClick={() => setSidebarOpen(false)}
                        className="absolute inset-0 bg-slate-950/40"
                    />
                    <div className="absolute inset-y-0 left-0 z-50 w-[272px] max-w-[88vw] bg-white shadow-2xl">
                        <Sidebar onNavigateComplete={() => setSidebarOpen(false)} mobile />
                    </div>
                </div>
            )}

            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                <Header onToggleSidebar={() => setSidebarOpen((current) => !current)} />
                <main className="flex-1 overflow-y-auto p-8">
                    <div className="mx-auto max-w-[1400px] space-y-6">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
