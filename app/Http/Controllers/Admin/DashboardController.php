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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $dashboardStartedAt = microtime(true);
        $seoTotal = count(SeoPageResolver::pages());
        $seoConfigured = SeoMeta::query()
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('keywords')
            ->count();

        $contactsUnread = ContactRequest::query()->where('is_read', false)->count();
        $quotesUnread = QuoteRequest::query()->where('is_read', false)->count();
        $catalogUnread = CatalogRequest::query()->where('is_read', false)->count();
        $today = now()->endOfDay();
        $timelineStart = now()->subDays(13)->startOfDay();
        $heatmapStart = now()->subDays(83)->startOfDay();

        $dbProbeStartedAt = microtime(true);
        $leadSeries = $this->buildLeadSeries($timelineStart, $today);
        $dbProbeMs = round((microtime(true) - $dbProbeStartedAt) * 1000, 1);

        $logProbeStartedAt = microtime(true);
        $logEntries = $this->readRecentLogEntries(storage_path('logs/laravel.log'));
        $logProbeMs = round((microtime(true) - $logProbeStartedAt) * 1000, 1);

        $security = $this->buildSecurityMetrics($logEntries, $timelineStart);
        $errors = $this->buildErrorMetrics($logEntries, $timelineStart);
        $heatmap = $this->buildHeatmapSeries($heatmapStart, $today, $logEntries);

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

        $dashboardMs = round((microtime(true) - $dashboardStartedAt) * 1000, 1);
        $memoryMb = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
        $logSizeMb = File::exists(storage_path('logs/laravel.log'))
            ? round((float) File::size(storage_path('logs/laravel.log')) / 1024 / 1024, 2)
            : 0.0;

        $systemBenchmarks = [
            [
                'label' => 'Respuesta del panel',
                'value' => $dashboardMs,
                'unit' => 'ms',
                'goal' => 250,
                'tone' => $dashboardMs <= 250 ? 'emerald' : ($dashboardMs <= 600 ? 'amber' : 'rose'),
            ],
            [
                'label' => 'Consulta de datos',
                'value' => $dbProbeMs,
                'unit' => 'ms',
                'goal' => 120,
                'tone' => $dbProbeMs <= 120 ? 'emerald' : ($dbProbeMs <= 260 ? 'amber' : 'rose'),
            ],
            [
                'label' => 'Lectura de logs',
                'value' => $logProbeMs,
                'unit' => 'ms',
                'goal' => 150,
                'tone' => $logProbeMs <= 150 ? 'emerald' : ($logProbeMs <= 300 ? 'amber' : 'rose'),
            ],
            [
                'label' => 'Memoria usada',
                'value' => $memoryMb,
                'unit' => 'MB',
                'goal' => 96,
                'tone' => $memoryMb <= 96 ? 'sky' : ($memoryMb <= 160 ? 'amber' : 'rose'),
            ],
        ];

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
            'analytics' => [
                'leadSeries' => $leadSeries,
                'heatmap' => $heatmap,
                'securitySeries' => $security['series'],
                'channelMix' => $this->buildChannelMix(),
                'systemBenchmarks' => $systemBenchmarks,
                'systemSummary' => [
                    'log_size_mb' => $logSizeMb,
                    'events_scanned' => $logEntries->count(),
                    'last_log_event' => $logEntries->first()['time'] ?? null,
                ],
                'security' => [
                    'blocked_24h' => $security['blocked_24h'],
                    'blocked_7d' => $security['blocked_7d'],
                    'rate_limits_7d' => $security['rate_limits_7d'],
                    'denied_admin_7d' => $security['denied_admin_7d'],
                    'suspected_bots_7d' => $security['suspected_bots_7d'],
                    'top_agents' => $security['top_agents'],
                    'top_paths' => $security['top_paths'],
                ],
                'errors' => [
                    'error_24h' => $errors['error_24h'],
                    'error_7d' => $errors['error_7d'],
                    'warning_7d' => $errors['warning_7d'],
                    'series' => $errors['series'],
                    'recent' => $errors['recent'],
                ],
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

    protected function buildLeadSeries(Carbon $start, Carbon $end): array
    {
        $contacts = $this->dailyCounts(ContactRequest::query(), $start);
        $quotes = $this->dailyCounts(QuoteRequest::query(), $start);
        $catalog = $this->dailyCounts(CatalogRequest::query(), $start);
        $newsletter = $this->dailyCounts(NewsletterSubscriber::query(), $start);

        return $this->iterateDays($start, $end)
            ->map(function (Carbon $day) use ($contacts, $quotes, $catalog, $newsletter): array {
                $date = $day->toDateString();
                $contactCount = (int) ($contacts[$date] ?? 0);
                $quoteCount = (int) ($quotes[$date] ?? 0);
                $catalogCount = (int) ($catalog[$date] ?? 0);
                $newsletterCount = (int) ($newsletter[$date] ?? 0);

                return [
                    'date' => $date,
                    'label' => $day->format('d M'),
                    'contactos' => $contactCount,
                    'presupuestos' => $quoteCount,
                    'catalogos' => $catalogCount,
                    'suscripciones' => $newsletterCount,
                    'total' => $contactCount + $quoteCount + $catalogCount + $newsletterCount,
                ];
            })
            ->values()
            ->all();
    }

    protected function buildChannelMix(): array
    {
        return [
            [
                'name' => 'Contacto',
                'value' => ContactRequest::query()->count(),
                'unread' => ContactRequest::query()->where('is_read', false)->count(),
                'color' => '#25A7CA',
            ],
            [
                'name' => 'Presupuesto',
                'value' => QuoteRequest::query()->count(),
                'unread' => QuoteRequest::query()->where('is_read', false)->count(),
                'color' => '#0F766E',
            ],
            [
                'name' => 'Catálogo',
                'value' => CatalogRequest::query()->count(),
                'unread' => CatalogRequest::query()->where('is_read', false)->count(),
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Newsletter',
                'value' => NewsletterSubscriber::query()->count(),
                'unread' => 0,
                'color' => '#7C3AED',
            ],
        ];
    }

    protected function buildSecurityMetrics(Collection $entries, Carbon $start): array
    {
        $recentEntries = $entries->filter(
            fn (array $entry): bool => $entry['occurred_at']->greaterThanOrEqualTo($start)
        );

        $blockedEntries = $recentEntries->filter(
            fn (array $entry): bool => Str::contains($entry['message'], [
                'Blocked public form submission',
                'Denied admin panel access',
                'rate limit',
            ])
        );

        $rateLimitEntries = $recentEntries->filter(
            fn (array $entry): bool => Str::contains($entry['message'], 'rate limit')
        );

        $deniedAdminEntries = $recentEntries->filter(
            fn (array $entry): bool => Str::contains($entry['message'], 'Denied admin panel access')
        );

        $suspectedBotEntries = $recentEntries->filter(function (array $entry): bool {
            if ($this->looksLikeBot($entry['user_agent'])) {
                return true;
            }

            return Str::contains($entry['message'], [
                'honeypot',
                'Denied admin panel access',
                'rate limit',
            ]);
        });

        $topAgents = $suspectedBotEntries
            ->groupBy(fn (array $entry): string => $entry['user_agent'] ?: 'Sin agente identificado')
            ->map(fn (Collection $group, string $agent): array => [
                'agent' => Str::limit($agent, 58),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->all();

        $topPaths = $blockedEntries
            ->groupBy(fn (array $entry): string => $entry['path'] ?: 'Ruta no disponible')
            ->map(fn (Collection $group, string $path): array => [
                'path' => $path,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->all();

        $securityDaily = $this->iterateDays($start, now()->endOfDay())
            ->map(function (Carbon $day) use ($blockedEntries, $deniedAdminEntries, $rateLimitEntries): array {
                $date = $day->toDateString();

                return [
                    'date' => $date,
                    'label' => $day->format('d M'),
                    'bloqueos' => $blockedEntries->filter(
                        fn (array $entry): bool => $entry['date'] === $date
                    )->count(),
                    'accesos' => $deniedAdminEntries->filter(
                        fn (array $entry): bool => $entry['date'] === $date
                    )->count(),
                    'limites' => $rateLimitEntries->filter(
                        fn (array $entry): bool => $entry['date'] === $date
                    )->count(),
                ];
            })
            ->values()
            ->all();

        return [
            'blocked_24h' => $blockedEntries->filter(
                fn (array $entry): bool => $entry['occurred_at']->greaterThanOrEqualTo(now()->subDay())
            )->count(),
            'blocked_7d' => $blockedEntries->count(),
            'rate_limits_7d' => $rateLimitEntries->count(),
            'denied_admin_7d' => $deniedAdminEntries->count(),
            'suspected_bots_7d' => $suspectedBotEntries->count(),
            'top_agents' => $topAgents,
            'top_paths' => $topPaths,
            'series' => $securityDaily,
        ];
    }

    protected function buildErrorMetrics(Collection $entries, Carbon $start): array
    {
        $recentEntries = $entries->filter(
            fn (array $entry): bool => $entry['occurred_at']->greaterThanOrEqualTo($start)
        );

        $errorEntries = $recentEntries->filter(
            fn (array $entry): bool => in_array($entry['level'], ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'], true)
        );

        $warningEntries = $recentEntries->filter(
            fn (array $entry): bool => in_array($entry['level'], ['WARNING', 'NOTICE'], true)
        );

        $series = $this->iterateDays($start, now()->endOfDay())
            ->map(function (Carbon $day) use ($errorEntries, $warningEntries): array {
                $date = $day->toDateString();

                return [
                    'date' => $date,
                    'label' => $day->format('d M'),
                    'errores' => $errorEntries->filter(
                        fn (array $entry): bool => $entry['date'] === $date
                    )->count(),
                    'alertas' => $warningEntries->filter(
                        fn (array $entry): bool => $entry['date'] === $date
                    )->count(),
                ];
            })
            ->values()
            ->all();

        $recent = $errorEntries
            ->sortByDesc(fn (array $entry): int => $entry['occurred_at']->timestamp)
            ->take(6)
            ->map(fn (array $entry): array => [
                'time' => $entry['time'],
                'message' => Str::limit($entry['message'], 110),
                'path' => $entry['path'],
                'level' => strtolower($entry['level']),
                'user_agent' => $entry['user_agent'] ? Str::limit($entry['user_agent'], 58) : null,
            ])
            ->values()
            ->all();

        return [
            'error_24h' => $errorEntries->filter(
                fn (array $entry): bool => $entry['occurred_at']->greaterThanOrEqualTo(now()->subDay())
            )->count(),
            'error_7d' => $errorEntries->count(),
            'warning_7d' => $warningEntries->count(),
            'series' => $series,
            'recent' => $recent,
        ];
    }

    protected function buildHeatmapSeries(Carbon $start, Carbon $end, Collection $entries): array
    {
        $contacts = $this->dailyCounts(ContactRequest::query(), $start);
        $quotes = $this->dailyCounts(QuoteRequest::query(), $start);
        $catalog = $this->dailyCounts(CatalogRequest::query(), $start);
        $newsletter = $this->dailyCounts(NewsletterSubscriber::query(), $start);

        $logCounts = $entries
            ->filter(fn (array $entry): bool => $entry['occurred_at']->greaterThanOrEqualTo($start))
            ->groupBy('date')
            ->map(fn (Collection $group): int => $group->count());

        return $this->iterateDays($start, $end)
            ->map(function (Carbon $day) use ($contacts, $quotes, $catalog, $newsletter, $logCounts): array {
                $date = $day->toDateString();
                $count =
                    (int) ($contacts[$date] ?? 0) +
                    (int) ($quotes[$date] ?? 0) +
                    (int) ($catalog[$date] ?? 0) +
                    (int) ($newsletter[$date] ?? 0) +
                    (int) ($logCounts[$date] ?? 0);

                return [
                    'date' => $date,
                    'count' => $count,
                ];
            })
            ->values()
            ->all();
    }

    protected function dailyCounts(Builder $query, Carbon $start): array
    {
        return $query
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->map(fn ($value): int => (int) $value)
            ->all();
    }

    protected function iterateDays(Carbon $start, Carbon $end): Collection
    {
        $cursor = $start->copy()->startOfDay();
        $days = collect();

        while ($cursor->lessThanOrEqualTo($end)) {
            $days->push($cursor->copy());
            $cursor->addDay();
        }

        return $days;
    }

    protected function readRecentLogEntries(string $path, int $maxLines = 1800): Collection
    {
        if (! File::exists($path)) {
            return collect();
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines)) {
            return collect();
        }

        return collect(array_slice($lines, -$maxLines))
            ->filter(fn (string $line): bool => Str::startsWith($line, '['))
            ->map(fn (string $line): ?array => $this->parseLogLine($line))
            ->filter()
            ->sortByDesc(fn (array $entry): int => $entry['occurred_at']->timestamp)
            ->values();
    }

    protected function parseLogLine(string $line): ?array
    {
        if (! preg_match('/^\[(?<date>[^\]]+)\]\s+\w+\.(?<level>[A-Z]+):\s+(?<rest>.*)$/', $line, $matches)) {
            return null;
        }

        try {
            $occurredAt = Carbon::parse($matches['date']);
        } catch (\Throwable) {
            return null;
        }

        $rest = trim($matches['rest']);
        $context = [];
        $message = $rest;

        if (preg_match('/^(?<message>.*)\s(?<json>\{.*\})$/', $rest, $contextMatches)) {
            $decoded = json_decode($contextMatches['json'], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $message = trim($contextMatches['message']);
                $context = $decoded;
            }
        }

        return [
            'date' => $occurredAt->toDateString(),
            'time' => $occurredAt->format('d/m/Y H:i'),
            'occurred_at' => $occurredAt,
            'level' => strtoupper($matches['level']),
            'message' => $message,
            'path' => $context['path'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ];
    }

    protected function looksLikeBot(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        return (bool) preg_match(
            '/bot|spider|crawler|crawl|headless|python|curl|wget|postman|scrapy|httpclient|phantom|selenium/i',
            $userAgent
        );
    }
}
