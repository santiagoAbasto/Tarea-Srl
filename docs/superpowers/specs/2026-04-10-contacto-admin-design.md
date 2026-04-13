# Diseño: Sección Contacto — Panel Admin

**Fecha:** 2026-04-10  
**Stack:** Laravel 12 + React + Inertia.js + Tailwind CSS v4

---

## Contexto

El sidebar del admin ya tiene el bloque "Contacto" definido con dos hijos, pero ambos apuntan a URLs stub (`/admin/dashboard?module=contact-*`) que no llevan a páginas reales. El backend (modelos, controladores API y rutas) ya existe. Este diseño cubre la creación de las páginas Inertia faltantes y la actualización del sidebar.

---

## Arquitectura

### Rutas nuevas

Dentro del grupo admin autenticado en `routes/web.php`:

```php
Route::get('/contacto/settings', [ContactController::class, 'settingsPage'])->name('admin.contact.settings');
Route::get('/contacto/requests', [ContactController::class, 'requestsPage'])->name('admin.contact.requests');
```

### Controlador Inertia

`App\Http\Controllers\Admin\ContactController`

**`settingsPage()`**
- Hace `ContactPageSetting::firstOrCreate([])` para garantizar que siempre haya un registro.
- Retorna `Inertia::render('Admin/Contacts/Settings', ['settings' => $settings])`.

**`requestsPage()`**
- Lista `ContactRequest::orderByDesc('created_at')->get()`.
- Calcula stats: `total` y `unread` (donde `is_read = false`).
- Construye `reply_url` para cada item: `mailto:{email}?subject=Re: Consulta` (mismo patrón que `reply_url` en Catálogo).
- Retorna `Inertia::render('Admin/Contacts/Requests', ['requests' => $requests, 'stats' => $stats])`.

---

## Páginas React

### `Admin/Contacts/Settings.jsx`

- **Header:** gradiente `radial-gradient` igual al de Catálogo. Badge con ícono `solar:chat-round-outline`. Título "Gestión de contacto". Botón "Ver página pública" (link a `/contacto`).
- **Formulario:** edita `ContactPageSetting` vía `PUT /admin/api/contact-page-settings` con `axios`. Campos: `address` (textarea), `phone_primary`, `phone_secondary`, `email_primary`, `email_secondary`, `map_link` (inputs text), `map_iframe` (textarea). Botón "Guardar" con estado loading. Toast de éxito/error con `emitAdminToast`.
- **Sección card:** un solo `<section>` con `rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm` — mismo estilo que Catálogo.

### `Admin/Contacts/Requests.jsx`

- **Header:** mismo gradiente. Badge con ícono `solar:inbox-outline`. Título "Consultas recibidas".
- **Stats cards:** dos cards — "Consultas totales" y "Sin leer" — con `StatCard` (mismo componente local que Catálogo).
- **Listado:** cards por consulta (`rounded-[26px] border p-5`). Fondo `bg-[#25A7CA]/5 border-[#25A7CA]/30` si no leída, `bg-slate-50 border-slate-200` si leída.
- **Datos mostrados por card:** nombre completo (`first_name + last_name`), email, teléfono, fecha, mensaje, badge "Nueva"/"Leída", badge de status.
- **Acciones por card:**
  - Marcar leída / no leída → `PUT /admin/api/contact-requests/{id}` con `{ is_read: true/false }`
  - Marcar resuelta → `PUT` con `{ status: 'resuelta', is_read: true }`
  - Responder → `<a href={reply_url}>` donde `reply_url = mailto:{email}?subject=Re: Consulta` — abre la app de correo nativa
  - Eliminar → `DELETE /admin/api/contact-requests/{id}` con confirm previo
- **Estado vacío:** mismo bloque con borde dashed que Catálogo.

---

## Sidebar

Actualizar las URLs de los dos hijos del bloque "Contacto" en `Sidebar.jsx`:

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
}
```

---

## Línea gráfica

Todos los estilos siguen exactamente los tokens del proyecto:
- Color primario: `#25A7CA` / hover: `#1d96b8` / texto activo: `#117a98`
- Radios: `rounded-2xl` (botones/inputs), `rounded-[28px]`/`rounded-[30px]` (cards), `rounded-[32px]` (header)
- Componentes locales reutilizados: `StatCard`, `Field`, `Toggle` — definidos inline en cada página, igual que Catálogo
- Iconos: `@iconify/react` con íconos `solar:*`
- Toasts: `emitAdminToast` de `@/lib/adminToast`

---

## Archivos a crear / modificar

| Acción | Archivo |
|--------|---------|
| Crear | `app/Http/Controllers/Admin/ContactController.php` |
| Crear | `resources/js/Pages/Admin/Contacts/Settings.jsx` |
| Crear | `resources/js/Pages/Admin/Contacts/Requests.jsx` |
| Modificar | `routes/web.php` — agregar 2 rutas GET |
| Modificar | `resources/js/Components/Admin/Sidebar.jsx` — actualizar URLs del bloque Contacto |
