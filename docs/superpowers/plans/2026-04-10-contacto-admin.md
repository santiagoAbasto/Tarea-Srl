# Contacto Admin — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear dos páginas Inertia dedicadas para la sección Contacto del admin (`/admin/contacto/settings` y `/admin/contacto/requests`) y actualizar el sidebar para apuntar a ellas.

**Architecture:** Un controlador Inertia `ContactController` con dos métodos (`settingsPage` / `requestsPage`), dos páginas React que siguen exactamente el patrón visual de `Admin/Catalog/Index.jsx`, y el modelo `ContactRequest` con los accessors `reply_url` y `reply_email` igual que `CatalogRequest`.

**Tech Stack:** Laravel 12, Inertia.js, React, Tailwind CSS v4, `@iconify/react` (íconos `solar:*`), `axios`, `emitAdminToast`.

---

## File Map

| Acción | Archivo | Responsabilidad |
|--------|---------|-----------------|
| Modificar | `app/Models/ContactRequest.php` | Agregar `$appends`, `getReplyEmailAttribute`, `getReplyUrlAttribute` |
| Crear | `app/Http/Controllers/Admin/ContactController.php` | Dos métodos Inertia: settings y requests |
| Modificar | `routes/web.php` | Agregar import + 2 rutas GET admin |
| Crear | `resources/js/Pages/Admin/Contacts/Settings.jsx` | Formulario de edición de `ContactPageSetting` |
| Crear | `resources/js/Pages/Admin/Contacts/Requests.jsx` | Listado de consultas con acciones |
| Modificar | `resources/js/Components/Admin/Sidebar.jsx` | Actualizar URLs del bloque Contacto |
| Crear | `tests/Feature/Cms/ContactAdminPagesTest.php` | Tests de acceso a las dos rutas nuevas |

---

## Task 1: Agregar `reply_url` al modelo `ContactRequest`

**Files:**
- Modify: `app/Models/ContactRequest.php`
- Test: `tests/Feature/Cms/ContactAdminPagesTest.php`

- [ ] **Step 1: Escribir el test que falla**

Crear `tests/Feature/Cms/ContactAdminPagesTest.php`:

```php
<?php

namespace Tests\Feature\Cms;

use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        config(['security.admin_allowed_emails' => ['admin@example.com']]);

        return User::factory()->create([
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
        ]);
    }

    public function test_contact_request_reply_url_is_a_mailto_link(): void
    {
        $request = new ContactRequest([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
        ]);

        $this->assertStringStartsWith('mailto:juan@example.com', $request->reply_url);
        $this->assertStringContainsString('subject=', $request->reply_url);
    }

    public function test_contact_request_reply_url_is_null_when_no_email(): void
    {
        $request = new ContactRequest([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        $this->assertNull($request->reply_url);
    }

    public function test_settings_page_requires_auth(): void
    {
        $this->get('/admin/contacto/settings')->assertRedirect('/admin/login');
    }

    public function test_requests_page_requires_auth(): void
    {
        $this->get('/admin/contacto/requests')->assertRedirect('/admin/login');
    }

    public function test_settings_page_returns_ok_for_admin(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/admin/contacto/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Contacts/Settings'));
    }

    public function test_requests_page_returns_ok_for_admin(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/admin/contacto/requests')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Contacts/Requests'));
    }

    public function test_requests_page_passes_stats_to_view(): void
    {
        ContactRequest::factory()->create(['email' => 'a@b.com', 'is_read' => false]);
        ContactRequest::factory()->create(['email' => 'c@d.com', 'is_read' => true]);

        $this->actingAs($this->adminUser())
            ->get('/admin/contacto/requests')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('stats')
                ->where('stats.total', 2)
                ->where('stats.unread', 1)
            );
    }
}
```

- [ ] **Step 2: Correr los tests para verificar que fallan**

```bash
cd "/Users/user/Desktop/Tarea SRL" && composer test -- --filter ContactAdminPagesTest
```

Esperado: varios FAIL / Error (métodos y rutas aún no existen).

- [ ] **Step 3: Agregar `reply_url` al modelo `ContactRequest`**

Reemplazar el contenido completo de `app/Models/ContactRequest.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ContactRequest extends Model
{
    use HasFactory;

    protected $appends = [
        'reply_email',
        'reply_url',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
        'is_read',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function getReplyEmailAttribute(): ?string
    {
        return $this->email ?: null;
    }

    public function getReplyUrlAttribute(): ?string
    {
        if (! $this->email) {
            return null;
        }

        $name = trim($this->first_name.' '.$this->last_name);
        $subject = 'Re: Consulta - Tarea SRL';
        $lines = array_filter([
            'Hola'.($name ? ' '.$name : '').',',
            '',
            'Gracias por comunicarte con Tarea S.R.L.',
            'Quedamos a disposición para responder tu consulta.',
            '',
            'Saludos,',
            'Equipo comercial Tarea S.R.L.',
        ]);

        $query = Arr::query([
            'subject' => $subject,
            'body'    => implode("\n", $lines),
        ]);

        return 'mailto:'.$this->email.'?'.$query;
    }
}
```

- [ ] **Step 4: Verificar los dos tests del modelo**

```bash
cd "/Users/user/Desktop/Tarea SRL" && composer test -- --filter "test_contact_request_reply_url"
```

Esperado: 2 tests PASS.

---

## Task 2: Crear `ContactController` (Inertia)

**Files:**
- Create: `app/Http/Controllers/Admin/ContactController.php`

- [ ] **Step 1: Crear el controlador**

Crear `app/Http/Controllers/Admin/ContactController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPageSetting;
use App\Models\ContactRequest;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function settingsPage(): Response
    {
        $settings = ContactPageSetting::query()->firstOrCreate([]);

        return Inertia::render('Admin/Contacts/Settings', [
            'settings' => [
                'id'              => $settings->id,
                'address'         => $settings->address,
                'phone_primary'   => $settings->phone_primary,
                'phone_secondary' => $settings->phone_secondary,
                'email_primary'   => $settings->email_primary,
                'email_secondary' => $settings->email_secondary,
                'map_iframe'      => $settings->map_iframe,
                'map_link'        => $settings->map_link,
            ],
        ]);
    }

    public function requestsPage(): Response
    {
        $requests = ContactRequest::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ContactRequest $request) => [
                'id'         => $request->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'message'    => $request->message,
                'is_read'    => $request->is_read,
                'status'     => $request->status,
                'reply_url'  => $request->reply_url,
                'created_at' => optional($request->created_at)?->format('d/m/Y H:i'),
            ])
            ->values();

        return Inertia::render('Admin/Contacts/Requests', [
            'requests' => $requests,
            'stats'    => [
                'total'  => ContactRequest::query()->count(),
                'unread' => ContactRequest::query()->where('is_read', false)->count(),
            ],
        ]);
    }
}
```

---

## Task 3: Agregar rutas en `web.php`

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Agregar el import del controlador**

En `routes/web.php`, agregar junto a los otros imports de Admin:

```php
use App\Http\Controllers\Admin\ContactController;
```

- [ ] **Step 2: Agregar las dos rutas GET dentro del grupo admin**

Dentro del bloque `Route::prefix('admin')->...->group(function () { Route::middleware(['auth', 'admin.access'])->group(function () {`, agregar después de la ruta de `novedades/{id}/edit`:

```php
Route::get('/contacto/settings', [ContactController::class, 'settingsPage'])->name('contact.settings');
Route::get('/contacto/requests', [ContactController::class, 'requestsPage'])->name('contact.requests');
```

- [ ] **Step 3: Correr los tests de acceso**

```bash
cd "/Users/user/Desktop/Tarea SRL" && composer test -- --filter "test_settings_page|test_requests_page"
```

Esperado: 4 tests PASS (auth redirect + 200 para admin). Los tests de Inertia component fallarán hasta que existan las páginas React — es normal.

---

## Task 4: Crear `Settings.jsx`

**Files:**
- Create: `resources/js/Pages/Admin/Contacts/Settings.jsx`

- [ ] **Step 1: Crear la página**

Crear `resources/js/Pages/Admin/Contacts/Settings.jsx`:

```jsx
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useState } from "react";

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

export default function ContactSettings({ settings: initialSettings }) {
    const [form, setForm] = useState({
        address:         initialSettings?.address         ?? "",
        phone_primary:   initialSettings?.phone_primary   ?? "",
        phone_secondary: initialSettings?.phone_secondary ?? "",
        email_primary:   initialSettings?.email_primary   ?? "",
        email_secondary: initialSettings?.email_secondary ?? "",
        map_iframe:      initialSettings?.map_iframe      ?? "",
        map_link:        initialSettings?.map_link        ?? "",
    });
    const [saving, setSaving] = useState(false);

    const handleChange = (key) => (event) =>
        setForm((current) => ({ ...current, [key]: event.target.value }));

    const save = async () => {
        setSaving(true);

        try {
            await axios.put("/admin/api/contact-page-settings", form);
            emitAdminToast("Los datos de contacto se actualizaron correctamente.");
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudieron guardar los datos de contacto.",
                "error",
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Datos de contacto" />

            <div className="space-y-6">
                {/* Header */}
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div className="max-w-3xl">
                                <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                    <Icon icon="solar:chat-round-outline" width={14} />
                                    Contacto / Datos
                                </div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Datos de contacto
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                    Desde acá administrás la dirección, teléfonos, emails
                                    y el mapa que se muestran en la página pública de contacto.
                                </p>
                            </div>

                            <a
                                href="/contacto"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98]"
                            >
                                <Icon icon="solar:square-arrow-right-up-outline" width={18} />
                                Ver página pública
                            </a>
                        </div>
                    </div>
                </section>

                {/* Form card */}
                <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-6">
                        <h2 className="text-2xl font-semibold text-slate-900">
                            Información de contacto
                        </h2>
                        <p className="mt-2 text-sm text-slate-500">
                            Estos datos se muestran en la sección de contacto del sitio público.
                        </p>
                    </div>

                    <div className="space-y-5">
                        <Field label="Dirección">
                            <textarea
                                value={form.address}
                                onChange={handleChange("address")}
                                rows={3}
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            />
                        </Field>

                        <div className="grid gap-5 md:grid-cols-2">
                            <Field label="Teléfono principal">
                                <input
                                    type="text"
                                    value={form.phone_primary}
                                    onChange={handleChange("phone_primary")}
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </Field>

                            <Field label="Teléfono secundario">
                                <input
                                    type="text"
                                    value={form.phone_secondary}
                                    onChange={handleChange("phone_secondary")}
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </Field>

                            <Field label="Email principal">
                                <input
                                    type="text"
                                    value={form.email_primary}
                                    onChange={handleChange("email_primary")}
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </Field>

                            <Field label="Email secundario">
                                <input
                                    type="text"
                                    value={form.email_secondary}
                                    onChange={handleChange("email_secondary")}
                                    className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                                />
                            </Field>
                        </div>

                        <Field
                            label="Link de Google Maps"
                            hint="URL directa al mapa (opcional)."
                        >
                            <input
                                type="text"
                                value={form.map_link}
                                onChange={handleChange("map_link")}
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            />
                        </Field>

                        <Field
                            label="Iframe del mapa"
                            hint="Pegá acá el código iframe de Google Maps."
                        >
                            <textarea
                                value={form.map_iframe}
                                onChange={handleChange("map_iframe")}
                                rows={5}
                                className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#25A7CA] focus:ring-4 focus:ring-[#25A7CA]/10"
                            />
                        </Field>

                        <div className="flex justify-end border-t border-slate-200 pt-5">
                            <button
                                type="button"
                                onClick={save}
                                disabled={saving}
                                className="rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                            >
                                {saving ? "Guardando..." : "Guardar datos"}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
```

---

## Task 5: Crear `Requests.jsx`

**Files:**
- Create: `resources/js/Pages/Admin/Contacts/Requests.jsx`

- [ ] **Step 1: Crear la página**

Crear `resources/js/Pages/Admin/Contacts/Requests.jsx`:

```jsx
import AdminLayout from "@/Layouts/AdminLayout";
import { emitAdminToast } from "@/lib/adminToast";
import { Head, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import axios from "axios";
import { useState } from "react";

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

export default function ContactRequests({ requests: initialRequests, stats }) {
    const [requests, setRequests] = useState(initialRequests ?? []);
    const [busyId, setBusyId] = useState(null);

    const reloadPage = () => {
        router.reload({
            onSuccess: (page) => {
                setRequests(page.props.requests ?? []);
            },
        });
    };

    const updateRequest = async (item, patch, successMessage) => {
        setBusyId(item.id);

        try {
            await axios.put(`/admin/api/contact-requests/${item.id}`, {
                first_name: item.first_name,
                last_name:  item.last_name,
                email:      item.email,
                phone:      item.phone,
                message:    item.message,
                is_read:    item.is_read,
                status:     item.status || "pendiente",
                ...patch,
            });

            emitAdminToast(successMessage);
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo actualizar la consulta.",
                "error",
            );
        } finally {
            setBusyId(null);
        }
    };

    const deleteRequest = async (item) => {
        const fullName = `${item.first_name} ${item.last_name}`.trim();

        if (!window.confirm(`¿Eliminar la consulta de "${fullName}"?`)) {
            return;
        }

        setBusyId(item.id);

        try {
            await axios.delete(`/admin/api/contact-requests/${item.id}`);
            emitAdminToast("La consulta se eliminó correctamente.");
            reloadPage();
        } catch (error) {
            emitAdminToast(
                error?.response?.data?.message ||
                    "No se pudo eliminar la consulta.",
                "error",
            );
        } finally {
            setBusyId(null);
        }
    };

    return (
        <AdminLayout>
            <Head title="Consultas recibidas" />

            <div className="space-y-6">
                {/* Header */}
                <section className="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
                    <div className="bg-[radial-gradient(circle_at_top_left,_rgba(37,167,202,0.16),_transparent_34%),linear-gradient(135deg,#f8fbfd_0%,#ffffff_44%,#eef7fb_100%)] px-6 py-8 md:px-8">
                        <div className="max-w-3xl">
                            <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#117a98]">
                                <Icon icon="solar:inbox-outline" width={14} />
                                Contacto / Consultas
                            </div>
                            <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                Consultas recibidas
                            </h1>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                                Revisá, marcá como leídas y respondé las consultas
                                que llegan desde el formulario público de contacto.
                            </p>
                        </div>
                    </div>
                </section>

                {/* Stats */}
                <section className="grid gap-4 md:grid-cols-2">
                    <StatCard
                        label="Consultas totales"
                        value={stats?.total ?? requests.length}
                        icon="solar:inbox-outline"
                    />
                    <StatCard
                        label="Sin leer"
                        value={stats?.unread ?? requests.filter((r) => !r.is_read).length}
                        icon="solar:letter-unread-outline"
                    />
                </section>

                {/* List */}
                <section className="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-6">
                        <h2 className="text-2xl font-semibold text-slate-900">
                            Bandeja de entrada
                        </h2>
                        <p className="mt-2 text-sm text-slate-500">
                            Las consultas nuevas aparecen resaltadas hasta que las marcás como leídas.
                        </p>
                    </div>

                    {requests.length ? (
                        <div className="space-y-4">
                            {requests.map((item) => {
                                const fullName = `${item.first_name} ${item.last_name}`.trim();

                                return (
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
                                                        {fullName || "Sin nombre"}
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
                                                    <p>
                                                        <strong className="text-slate-900">Email:</strong>{" "}
                                                        {item.email}
                                                    </p>
                                                    <p>
                                                        <strong className="text-slate-900">Teléfono:</strong>{" "}
                                                        {item.phone || "-"}
                                                    </p>
                                                    <p>
                                                        <strong className="text-slate-900">Fecha:</strong>{" "}
                                                        {item.created_at || "-"}
                                                    </p>
                                                </div>

                                                {item.message ? (
                                                    <div className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                                        {item.message}
                                                    </div>
                                                ) : null}
                                            </div>

                                            <div className="flex flex-wrap gap-3 xl:max-w-[340px] xl:justify-end">
                                                {!item.is_read ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            updateRequest(
                                                                item,
                                                                { is_read: true },
                                                                "La consulta se marcó como leída.",
                                                            )
                                                        }
                                                        disabled={busyId === item.id}
                                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98] disabled:opacity-60"
                                                    >
                                                        Marcar leída
                                                    </button>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            updateRequest(
                                                                item,
                                                                { is_read: false },
                                                                "La consulta se marcó como no leída.",
                                                            )
                                                        }
                                                        disabled={busyId === item.id}
                                                        className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#25A7CA] hover:text-[#117a98] disabled:opacity-60"
                                                    >
                                                        Marcar no leída
                                                    </button>
                                                )}

                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        updateRequest(
                                                            item,
                                                            { status: "resuelta", is_read: true },
                                                            "La consulta se marcó como resuelta.",
                                                        )
                                                    }
                                                    disabled={busyId === item.id}
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
                                                    disabled={busyId === item.id}
                                                    className="rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-60"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="rounded-[26px] border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                <Icon icon="solar:inbox-outline" width={24} />
                            </div>
                            <h3 className="mt-4 text-lg font-semibold text-slate-900">
                                Todavía no hay consultas
                            </h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Cuando alguien complete el formulario público de
                                contacto, la consulta aparecerá acá.
                            </p>
                        </div>
                    )}
                </section>
            </div>
        </AdminLayout>
    );
}
```

---

## Task 6: Actualizar el Sidebar

**Files:**
- Modify: `resources/js/Components/Admin/Sidebar.jsx`

- [ ] **Step 1: Reemplazar las URLs del bloque Contacto**

En `resources/js/Components/Admin/Sidebar.jsx`, localizar el objeto con `title: "Contacto"` (líneas ~316–333) y reemplazar los dos children:

```js
{
    title: "Contacto",
    description: "Datos y consultas",
    icon: "solar:chat-round-outline",
    children: [
        {
            title: "Datos de contacto",
            url: "/admin/contacto/settings",
            match: "/admin/contacto/settings",
        },
        {
            title: "Consultas recibidas",
            url: "/admin/contacto/requests",
            match: "/admin/contacto/requests",
            badge: unreadSubmissions > 0 ? unreadSubmissions : undefined,
        },
    ],
},
```

---

## Task 7: Correr todos los tests y verificar

- [ ] **Step 1: Correr suite completa**

```bash
cd "/Users/user/Desktop/Tarea SRL" && composer test
```

Esperado: todos los tests pasan. En particular `ContactAdminPagesTest` con 7 tests en verde.

- [ ] **Step 2: Commit**

```bash
cd "/Users/user/Desktop/Tarea SRL" && git add \
  app/Models/ContactRequest.php \
  app/Http/Controllers/Admin/ContactController.php \
  routes/web.php \
  resources/js/Pages/Admin/Contacts/Settings.jsx \
  resources/js/Pages/Admin/Contacts/Requests.jsx \
  resources/js/Components/Admin/Sidebar.jsx \
  tests/Feature/Cms/ContactAdminPagesTest.php \
  docs/superpowers/specs/2026-04-10-contacto-admin-design.md \
  docs/superpowers/plans/2026-04-10-contacto-admin.md

git commit -m "$(cat <<'EOF'
feat: add dedicated admin pages for Contacto section

- ContactController with settingsPage / requestsPage Inertia methods
- Settings.jsx: edit ContactPageSetting (address, phones, emails, map)
- Requests.jsx: list ContactRequest with read/resolve/reply/delete actions
- reply_url accessor on ContactRequest model (mailto: pattern, same as CatalogRequest)
- Sidebar URLs updated from ?module= stubs to real routes
- Feature tests for auth guard, 200 responses and stats props

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
EOF
)"
```
