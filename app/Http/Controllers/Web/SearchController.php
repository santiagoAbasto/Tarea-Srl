<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\ProductSubfamily;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->string('q')->toString());
        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first()
            ?: MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.search.index', [
            'q' => $query,
            'groups' => $query !== '' ? $this->searchGroups($query) : collect(),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->toString());

        if ($query === '') {
            return response()->json([
                'query' => '',
                'groups' => [],
            ]);
        }

        return response()->json([
            'query' => $query,
            'groups' => $this->searchGroups($query)->values(),
        ]);
    }

    protected function searchGroups(string $query)
    {
        return collect([
            $this->buildPagesGroup($query),
            $this->buildProductCategoriesGroup($query),
            $this->buildProductsGroup($query),
            $this->buildNewsGroup($query),
            $this->buildClientsGroup($query),
            $this->buildPartnersGroup($query),
        ])->filter(fn (array $group) => ! empty($group['items']))->values();
    }

    protected function buildPagesGroup(string $query): array
    {
        $pages = collect([
            [
                'title' => 'Nosotros',
                'slug' => 'nosotros',
                'url' => route('web.about'),
                'description' => 'Informacion institucional de Tarea SRL',
                'keywords' => 'empresa historia nosotros tarea institucional',
            ],
            [
                'title' => 'Productos',
                'slug' => 'productos',
                'url' => route('web.products.index'),
                'description' => 'Catalogo general de familias y productos',
                'keywords' => 'productos catalogo familias subfamilias',
            ],
            [
                'title' => 'Calidad',
                'slug' => 'calidad',
                'url' => route('web.quality'),
                'description' => 'Politicas y certificaciones de calidad',
                'keywords' => 'calidad politicas iso certificaciones seguridad',
            ],
            [
                'title' => 'Catalogo',
                'slug' => 'catalogo',
                'url' => route('web.catalog.show'),
                'description' => 'Solicitud de catalogo completo',
                'keywords' => 'catalogo solicitud descargar catalogo',
            ],
            [
                'title' => 'Representadas',
                'slug' => 'representadas',
                'url' => route('web.partners.index'),
                'description' => 'Marcas y empresas representadas',
                'keywords' => 'representadas marcas aliados partners',
            ],
            [
                'title' => 'Clientes',
                'slug' => 'clientes',
                'url' => route('web.clients.index'),
                'description' => 'Clientes y categorias atendidas',
                'keywords' => 'clientes energia industria telecomunicaciones',
            ],
            [
                'title' => 'Novedades',
                'slug' => 'novedades',
                'url' => route('web.news.index'),
                'description' => 'Noticias y novedades de la empresa',
                'keywords' => 'novedades noticias blog publicaciones',
            ],
            [
                'title' => 'Contacto',
                'slug' => 'contacto',
                'url' => route('web.contact.show'),
                'description' => 'Formulario y datos de contacto',
                'keywords' => 'contacto direccion telefono whatsapp email',
            ],
            [
                'title' => 'Presupuesto',
                'slug' => 'presupuesto',
                'url' => route('web.quote.show'),
                'description' => 'Solicitud de presupuesto',
                'keywords' => 'presupuesto cotizacion consulta productos',
            ],
        ]);

        $items = $pages
            ->map(function (array $page) use ($query) {
                $score = $this->scoreMatch($query, implode(' ', [
                    $page['title'],
                    $page['slug'],
                    $page['description'],
                    $page['keywords'],
                ]));

                if ($score === null) {
                    return null;
                }

                return [
                    'title' => $page['title'],
                    'url' => $page['url'],
                    'context' => 'Seccion del sitio',
                    'meta' => $page['description'],
                    'score' => $score,
                ];
            })
            ->filter()
            ->sortBy('score')
            ->take(6)
            ->values()
            ->map(fn (array $item) => collect($item)->except('score')->all())
            ->all();

        return [
            'title' => 'Paginas',
            'items' => $items,
        ];
    }

    protected function buildProductCategoriesGroup(string $query): array
    {
        $families = ProductFamily::query()
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(fn (ProductFamily $family) => [
                'title' => $family->name,
                'url' => route('web.products.family', $family->slug),
                'context' => 'Categoria principal',
                'meta' => 'Categoria principal',
            ]);

        $subfamilies = ProductSubfamily::query()
            ->with('family')
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('short_description', 'like', $like);
            })
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->filter(fn (ProductSubfamily $subfamily) => $subfamily->family)
            ->map(fn (ProductSubfamily $subfamily) => [
                'title' => $subfamily->name,
                'url' => route('web.products.family', $subfamily->family->slug).'?subfamily='.$subfamily->slug,
                'context' => 'Subcategoria',
                'meta' => 'Subcategoria de '.($subfamily->family->name ?? 'Productos'),
            ]);

        return [
            'title' => 'Categorias',
            'items' => $families->concat($subfamilies)->take(8)->values()->all(),
        ];
    }

    protected function buildProductsGroup(string $query): array
    {
        $items = Product::query()
            ->with(['family', 'subfamily'])
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $starts = $query.'%';

                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('material', 'like', $like)
                    ->orWhere('treatment', 'like', $like)
                    ->orWhere('observations', 'like', $like)
                    ->orWhereHas('family', fn ($family) => $family->where('name', 'like', $like))
                    ->orWhereHas('subfamily', fn ($subfamily) => $subfamily->where('name', 'like', $like));

                $builder->orderByRaw(
                    "case
                        when name like ? then 0
                        when sku like ? then 1
                        when name like ? then 2
                        when sku like ? then 3
                        else 4
                    end",
                    [$query, $query, $starts, $starts]
                );
            })
            ->limit(8)
            ->get()
            ->filter(fn (Product $product) => $product->family)
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'url' => route('web.products.show', [
                    'familySlug' => $product->family->slug,
                    'productSlug' => $product->slug,
                ]),
                'context' => 'Producto',
                'meta' => collect([$product->family?->name, $product->subfamily?->name])->filter()->join(' / '),
            ])
            ->values()
            ->all();

        return [
            'title' => 'Productos',
            'items' => $items,
        ];
    }

    protected function buildNewsGroup(string $query): array
    {
        $items = Post::query()
            ->with('category')
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder
                    ->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like)
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $like));
            })
            ->latest('published_at')
            ->limit(6)
            ->get()
            ->map(fn (Post $post) => [
                'title' => $post->title,
                'url' => route('web.news.show', $post->slug),
                'context' => 'Novedad',
                'meta' => $post->category?->name ?: 'Novedad',
            ])
            ->values()
            ->all();

        return [
            'title' => 'Novedades',
            'items' => $items,
        ];
    }

    protected function buildClientsGroup(string $query): array
    {
        $items = Client::query()
            ->with(['category', 'categories'])
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $like))
                    ->orWhereHas('categories', fn ($categories) => $categories->where('name', 'like', $like));
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(fn (Client $client) => [
                'title' => $client->name,
                'url' => route('web.clients.index').'#cliente-'.$client->slug,
                'context' => 'Cliente',
                'meta' => $client->category?->name ?: 'Cliente',
            ])
            ->values()
            ->all();

        return [
            'title' => 'Clientes',
            'items' => $items,
        ];
    }

    protected function buildPartnersGroup(string $query): array
    {
        $items = Partner::query()
            ->where('is_active', true)
            ->where(function ($builder) use ($query) {
                $like = '%'.$query.'%';
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('partner_type', 'like', $like);
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(fn (Partner $partner) => [
                'title' => $partner->name,
                'url' => route('web.partners.index').'#representada-'.$partner->slug,
                'context' => 'Representada',
                'meta' => Str::headline($partner->partner_type ?: 'Representada'),
            ])
            ->values()
            ->all();

        return [
            'title' => 'Representadas',
            'items' => $items,
        ];
    }

    protected function scoreMatch(string $query, string $haystack): ?int
    {
        $needle = $this->normalize($query);
        $text = $this->normalize($haystack);

        if ($needle === '' || $text === '') {
            return null;
        }

        if ($text === $needle) {
            return 0;
        }

        if (Str::startsWith($text, $needle)) {
            return 1;
        }

        if (Str::contains($text, ' '.$needle)) {
            return 2;
        }

        if (Str::contains($text, $needle)) {
            return 3;
        }

        return null;
    }

    protected function normalize(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    }
}
