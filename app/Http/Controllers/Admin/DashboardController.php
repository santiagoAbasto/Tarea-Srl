<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogRequest;
use App\Models\Client;
use App\Models\ContactRequest;
use App\Models\HomeHeroSlide;
use App\Models\NewsletterSubscriber;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\QuoteRequest;
use App\Models\SeoMeta;
use App\Models\SiteSection;
use App\Models\User;
use App\Support\SeoPageResolver;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $seoTotal = count(SeoPageResolver::pages());
        $seoConfigured = SeoMeta::query()
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('keywords')
            ->count();

        $contactsUnread = ContactRequest::query()->where('is_read', false)->count();
        $quotesUnread = QuoteRequest::query()->where('is_read', false)->count();
        $catalogUnread = CatalogRequest::query()->where('is_read', false)->count();

        $recentItems = collect()
            ->merge(
                Post::query()
                    ->latest('updated_at')
                    ->limit(3)
                    ->get()
                    ->map(fn (Post $post) => [
                        'type' => 'Novedad',
                        'title' => $post->title,
                        'meta' => $post->published_at?->format('d/m/Y') ?: 'Sin fecha',
                        'time' => optional($post->updated_at)?->format('d/m/Y H:i'),
                        'sort_at' => optional($post->updated_at)?->timestamp ?? 0,
                        'url' => "/admin/novedades/{$post->id}/edit",
                        'icon' => 'solar:document-text-outline',
                    ])
            )
            ->merge(
                ContactRequest::query()
                    ->latest('created_at')
                    ->limit(2)
                    ->get()
                    ->map(fn (ContactRequest $item) => [
                        'type' => 'Contacto',
                        'title' => trim(($item->first_name ?? '').' '.($item->last_name ?? '')) ?: ($item->email ?? 'Consulta web'),
                        'meta' => $item->email ?: 'Sin email',
                        'time' => optional($item->created_at)?->format('d/m/Y H:i'),
                        'sort_at' => optional($item->created_at)?->timestamp ?? 0,
                        'url' => '/admin/contact?tab=requests',
                        'icon' => 'solar:chat-round-outline',
                    ])
            )
            ->merge(
                QuoteRequest::query()
                    ->latest('created_at')
                    ->limit(2)
                    ->get()
                    ->map(fn (QuoteRequest $item) => [
                        'type' => 'Presupuesto',
                        'title' => $item->company ?: ($item->name ?: 'Solicitud de presupuesto'),
                        'meta' => $item->email ?: 'Sin email',
                        'time' => optional($item->created_at)?->format('d/m/Y H:i'),
                        'sort_at' => optional($item->created_at)?->timestamp ?? 0,
                        'url' => '/admin/quote?tab=requests',
                        'icon' => 'solar:document-text-outline',
                    ])
            )
            ->merge(
                CatalogRequest::query()
                    ->latest('created_at')
                    ->limit(2)
                    ->get()
                    ->map(fn (CatalogRequest $item) => [
                        'type' => 'Catálogo',
                        'title' => $item->company ?: ($item->name ?: 'Solicitud de catálogo'),
                        'meta' => $item->email ?: 'Sin email',
                        'time' => optional($item->created_at)?->format('d/m/Y H:i'),
                        'sort_at' => optional($item->created_at)?->timestamp ?? 0,
                        'url' => '/admin/catalog?tab=requests',
                        'icon' => 'solar:inbox-outline',
                    ])
            )
            ->sortByDesc('sort_at')
            ->map(function (array $item): array {
                unset($item['sort_at']);

                return $item;
            })
            ->take(8)
            ->values();

        $pendingItems = collect([
            [
                'title' => 'Consultas de contacto',
                'count' => $contactsUnread,
                'description' => 'Mensajes pendientes de leer o responder.',
                'url' => '/admin/contact?tab=requests',
                'icon' => 'solar:letter-unread-outline',
                'tone' => 'amber',
            ],
            [
                'title' => 'Solicitudes de presupuesto',
                'count' => $quotesUnread,
                'description' => 'Pedidos comerciales que todavía no se revisaron.',
                'url' => '/admin/quote?tab=requests',
                'icon' => 'solar:document-add-outline',
                'tone' => 'rose',
            ],
            [
                'title' => 'Pedidos de catálogo',
                'count' => $catalogUnread,
                'description' => 'Solicitudes que esperan seguimiento comercial.',
                'url' => '/admin/catalog?tab=requests',
                'icon' => 'solar:inbox-outline',
                'tone' => 'sky',
            ],
            [
                'title' => 'SEO incompleto',
                'count' => max($seoTotal - $seoConfigured, 0),
                'description' => 'Secciones todavía sin metadatos completos.',
                'url' => '/admin/seo',
                'icon' => 'solar:chart-2-outline',
                'tone' => 'emerald',
            ],
        ])
            ->filter(fn (array $item) => $item['count'] > 0)
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'users_with_access' => User::query()->where('can_access_admin', true)->count(),
                'products' => Product::query()->count(),
                'families' => ProductFamily::query()->count(),
                'posts' => Post::query()->count(),
                'clients' => Client::query()->count(),
                'partners' => Partner::query()->count(),
                'sections' => SiteSection::query()->count(),
                'contacts' => ContactRequest::query()->count(),
                'quotes' => QuoteRequest::query()->count(),
                'catalog_requests' => CatalogRequest::query()->count(),
                'newsletter' => NewsletterSubscriber::query()->count(),
                'active_home_slides' => HomeHeroSlide::query()->where('is_active', true)->count(),
            ],
            'inbox' => [
                'contacts_unread' => $contactsUnread,
                'quotes_unread' => $quotesUnread,
                'catalog_unread' => $catalogUnread,
            ],
            'seo' => [
                'configured' => $seoConfigured,
                'total' => $seoTotal,
                'completion' => $seoTotal > 0 ? (int) round(($seoConfigured / $seoTotal) * 100) : 0,
            ],
            'pendingItems' => $pendingItems,
            'recentItems' => $recentItems,
            'quickLinks' => [
                [
                    'group' => 'Home',
                    'title' => 'Home sliders',
                    'description' => 'Hero principal con imagen, video o YouTube',
                    'url' => '/admin/home/sliders',
                    'icon' => 'solar:gallery-wide-outline',
                ],
                [
                    'group' => 'Home',
                    'title' => 'Home nosotros',
                    'description' => 'Bloque azul institucional de la home',
                    'url' => '/admin/home/about',
                    'icon' => 'solar:widget-3-outline',
                ],
                [
                    'group' => 'Institucional',
                    'title' => 'Pagina Nosotros',
                    'description' => 'Banner, contenido, misión, visión y valores',
                    'url' => '/admin/about',
                    'icon' => 'solar:users-group-rounded-outline',
                ],
                [
                    'group' => 'Catálogo',
                    'title' => 'Productos',
                    'description' => 'Portada, familias, subfamilias y fichas del catálogo',
                    'url' => '/admin/products',
                    'icon' => 'solar:box-outline',
                ],
                [
                    'group' => 'Catálogo',
                    'title' => 'Catálogo',
                    'description' => 'Banner público y solicitudes del formulario',
                    'url' => '/admin/catalog',
                    'icon' => 'solar:document-text-outline',
                ],
                [
                    'group' => 'Institucional',
                    'title' => 'Calidad',
                    'description' => 'Banner, políticas y logos de certificación',
                    'url' => '/admin/quality',
                    'icon' => 'solar:shield-check-outline',
                ],
                [
                    'group' => 'Relaciones',
                    'title' => 'Clientes',
                    'description' => 'Banner, categorías y logos de clientes',
                    'url' => '/admin/clients?tab=page',
                    'icon' => 'solar:buildings-2-outline',
                ],
                [
                    'group' => 'Relaciones',
                    'title' => 'Representadas',
                    'description' => 'Marcas, portada y visibilidad en la web',
                    'url' => '/admin/representadas?tab=representadas',
                    'icon' => 'solar:users-group-two-rounded-outline',
                ],
                [
                    'group' => 'Contenido',
                    'title' => 'Novedades',
                    'description' => 'Publicaciones, categorías y contenido del blog',
                    'url' => '/admin/novedades?tab=novedades',
                    'icon' => 'solar:document-text-outline',
                ],
                [
                    'group' => 'Comercial',
                    'title' => 'Presupuesto',
                    'description' => 'Banner, formulario y solicitudes recibidas',
                    'url' => '/admin/quote?tab=page',
                    'icon' => 'solar:document-add-outline',
                ],
                [
                    'group' => 'Configuración',
                    'title' => 'Metadatos',
                    'description' => 'Google, WhatsApp y redes sociales',
                    'url' => '/admin/seo',
                    'icon' => 'solar:chart-2-outline',
                ],
                [
                    'group' => 'Configuración',
                    'title' => 'Usuarios',
                    'description' => 'Accesos, perfiles y permisos del panel',
                    'url' => '/admin/users',
                    'icon' => 'solar:users-group-rounded-outline',
                ],
            ],
        ]);
    }
}
