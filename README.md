# Tarea SRL

Base administrativa en Laravel + React para Tarea SRL.

## Stack tecnológico

- Laravel 12
- React + Inertia
- Tailwind CSS v4
- MySQL
- Laravel Breeze (auth)

## Arquitectura

Panel de administración:
- React + Inertia
- Rutas base: /admin/login y /admin/dashboard

## Instalación

Instalar dependencias:

`composer install`
`npm install`

Copiar variables de entorno:

`cp .env.example .env`

Generar key:

`php artisan key:generate`

Migraciones:

`php artisan migrate`

## Ejecutar

`php artisan serve`
`npm run dev`

## Tests

Usar:

`composer test`

El script limpia caches de Laravel antes de correr PHPUnit para evitar que una configuracion cacheada de produccion afecte la base de datos de testing.

## Seguridad

Variables relevantes:

- `ADMIN_ALLOWED_EMAILS`: lista blanca de emails que pueden entrar al panel `/admin`.
- `TRUSTED_PROXIES`: IPs o redes separadas por comas para proxies/CDN confiables.
- `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE`: endurecimiento de sesion para produccion HTTPS.
- `FORM_HONEYPOT_FIELD`, `FORM_RATE_LIMIT_MAX_ATTEMPTS`, `FORM_RATE_LIMIT_DECAY_SECONDS`: proteccion anti-spam para formularios publicos.
- `PAGE_MEDIA_IMAGE_MAX_KB`, `PAGE_MEDIA_VIDEO_MAX_KB`: limites de media del CMS.

Checklist previo a produccion:

- Definir `APP_ENV=production` y `APP_DEBUG=false`.
- Completar `ADMIN_ALLOWED_EMAILS` con cuentas reales autorizadas.
- Configurar `TRUSTED_PROXIES` con los proxies reales del despliegue.
- Activar `SESSION_SECURE_COOKIE=true` bajo HTTPS.
- Ejecutar `composer audit`.
- Verificar backups de base de datos y `storage/app/public`.
