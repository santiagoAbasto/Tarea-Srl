<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(): View
    {
        $pageData = $this->pageData();
        $families = $this->decorateFamilies(
            ProductFamily::query()
                ->with(['coverMedia', 'bannerMedia'])
                ->where('show_on_products_page', true)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        );

        return view('web.products.index', [
            'hero' => $pageData['hero'],
            'families' => $families,
            ...$pageData,
        ]);
    }

    public function all(Request $request): View|Response
    {
        $pageData = $this->pageData();
        $search = trim($request->string('q')->toString());
        $products = $this->productsQuery()
            ->when($search !== '', fn ($query) => $this->applyCatalogSearch($query, $search))
            ->paginate(16)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->view('web.products._catalog-results', [
                'mode' => 'all',
                'products' => $products,
            ]);
        }

        return view('web.products.catalog', [
            'hero' => $pageData['hero'],
            'mode' => 'all',
            'selectedFamily' => null,
            'selectedSubfamily' => null,
            'sidebarItems' => collect(),
            'search' => $search,
            'products' => $products,
            ...$pageData,
        ]);
    }

    public function family(Request $request, string $familySlug): View
    {
        $family = ProductFamily::query()
            ->with(['coverMedia', 'bannerMedia', 'subfamilies' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('slug', $familySlug)
            ->firstOrFail();
        $family->setAttribute('accent_color', $this->catalogAccentColorFor($family->slug) ?? $family->accent_color);
        $family->setRelation('subfamilies', $this->decorateSubfamilies($family->subfamilies));

        $selectedSubfamily = $request->string('subfamily')->toString();
        $pageData = $this->pageData();

        return view('web.products.catalog', [
            'hero' => $pageData['hero'],
            'mode' => 'family',
            'selectedFamily' => $family,
            'selectedSubfamily' => $selectedSubfamily !== '' ? $family->subfamilies->firstWhere('slug', $selectedSubfamily) : null,
            'sidebarItems' => $family->subfamilies,
            'products' => $this->productsQuery()
                ->where('product_family_id', $family->id)
                ->when($selectedSubfamily !== '', fn ($query) => $query->whereHas('subfamily', fn ($sub) => $sub->where('slug', $selectedSubfamily)))
                ->paginate(12)
                ->withQueryString(),
            ...$pageData,
        ]);
    }

    public function show(Request $request, string $familySlug, string $productSlug): View
    {
        $family = ProductFamily::query()
            ->with(['subfamilies' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('slug', $familySlug)
            ->firstOrFail();
        $family->setAttribute('accent_color', $this->catalogAccentColorFor($family->slug) ?? $family->accent_color);
        $family->setRelation('subfamilies', $this->decorateSubfamilies($family->subfamilies));

        $product = Product::query()
            ->with([
                'family',
                'subfamily',
                'mainMedia',
                'technicalSheet',
                'media.media',
                'specTables.columns',
                'specTables.rows.values.column',
                'relatedProducts.mainMedia',
                'relatedProducts.subfamily',
                'relatedProducts.family',
            ])
            ->where('product_family_id', $family->id)
            ->where('slug', $productSlug)
            ->firstOrFail();

        $pageData = $this->pageData();
        $gallery = $this->productGallery($product);
        $selectedMedia = $gallery->firstWhere('id', (int) $request->integer('media')) ?: $gallery->first();
        $relatedProducts = $product->relatedProducts
            ->where('is_active', true)
            ->take(3)
            ->values();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()
                ->with(['mainMedia', 'subfamily', 'family'])
                ->where('is_active', true)
                ->where('product_subfamily_id', $product->product_subfamily_id)
                ->whereKeyNot($product->id)
                ->orderBy('sort_order')
                ->limit(3)
                ->get();
        }

        return view('web.products.show', [
            'mode' => 'family',
            'hero' => $pageData['hero'],
            'selectedFamily' => $family,
            'selectedSubfamily' => $product->subfamily,
            'sidebarItems' => $family->subfamilies,
            'product' => $product,
            'gallery' => $gallery,
            'selectedMedia' => $selectedMedia,
            'specTable' => $product->specTables->first(),
            'relatedProducts' => $relatedProducts,
            ...$pageData,
        ]);
    }

    public function technicalSheet(string $familySlug, string $productSlug): Response
    {
        $family = ProductFamily::query()
            ->where('slug', $familySlug)
            ->firstOrFail();

        $product = Product::query()
            ->with([
                'family',
                'subfamily',
                'mainMedia',
                'technicalSheet',
                'media.media',
                'specTables.columns',
                'specTables.rows.values.column',
            ])
            ->where('product_family_id', $family->id)
            ->where('slug', $productSlug)
            ->firstOrFail();

        $gallery = $this->productGallery($product);
        $specTable = $product->specTables->first();

        $pdf = Pdf::loadView('web.products.technical-sheet', [
            'product' => $product,
            'gallery' => $gallery,
            'specTable' => $specTable,
            'mainImageDataUri' => $product->mainMedia ? $this->mediaDataUri($product->mainMedia) : null,
            'galleryDataUris' => $gallery->mapWithKeys(fn ($media) => [$media->id => $this->mediaDataUri($media)]),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($product->slug.'-ficha-tecnica.pdf');
    }

    protected function pageData(): array
    {
        $sections = SiteSection::query()
            ->with('media')
            ->where('page_key', 'productos')
            ->where('is_active', true)
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();
        $catalogHeaderLogo = MediaAsset::query()->where('path', 'uploads/branding/logosection2.png')->first();

        return [
            'hero' => $sections->get('hero'),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'catalogHeaderLogo' => $catalogHeaderLogo,
            'catalogHeaderLogoUrl' => $catalogHeaderLogo ? media_asset_url($catalogHeaderLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    protected function productsQuery()
    {
        return Product::query()
            ->with(['family', 'subfamily', 'mainMedia'])
            ->where('is_active', true)
            ->whereHas('family', fn ($query) => $query->where('show_on_products_page', true)->where('is_active', true))
            ->orderBy('product_family_id')
            ->orderBy('product_subfamily_id')
            ->orderBy('sort_order');
    }

    protected function applyCatalogSearch($query, string $search)
    {
        $like = '%'.$search.'%';
        $starts = $search.'%';

        return $query
            ->where(function ($builder) use ($like) {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('material', 'like', $like)
                    ->orWhere('treatment', 'like', $like)
                    ->orWhere('observations', 'like', $like)
                    ->orWhereHas('family', fn ($family) => $family->where('name', 'like', $like))
                    ->orWhereHas('subfamily', fn ($subfamily) => $subfamily->where('name', 'like', $like));
            })
            ->orderByRaw(
                "case
                    when name like ? then 0
                    when sku like ? then 1
                    when name like ? then 2
                    when sku like ? then 3
                    else 4
                end",
                [$search, $search, $starts, $starts]
            );
    }

    protected function productGallery(Product $product): Collection
    {
        $items = collect();

        if ($product->mainMedia) {
            $items->push($product->mainMedia);
        }

        foreach ($product->media as $mediaItem) {
            if ($mediaItem->media) {
                $items->push($mediaItem->media);
            }
        }

        return $items->unique('id')->values();
    }

    protected function mediaDataUri(MediaAsset $media): ?string
    {
        $disk = $media->disk ?? 'public';
        $path = $media->path;

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $contents = Storage::disk($disk)->get($path);
        $mime = $media->mime_type ?: Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    protected function decorateFamilies($families)
    {
        return $families->map(function (ProductFamily $family) {
            $family->setAttribute('accent_color', $this->catalogAccentColorFor($family->slug) ?? $family->accent_color);

            return $family;
        });
    }

    protected function decorateSubfamilies($subfamilies)
    {
        return $subfamilies->map(function ($subfamily) {
            $subfamily->setAttribute('accent_color', $this->catalogSubfamilyAccentColorFor($subfamily->slug) ?? $subfamily->accent_color);

            return $subfamily;
        });
    }

    protected function catalogAccentColorFor(string $slug): ?string
    {
        return [
            'herrajes-y-morseteria' => '#093C62',
            'seccionadores-y-descargadores' => '#8BCFDE',
            'aisladores' => '#A46DA9',
            'elementos-pat' => '#E85556',
            'cables' => '#FBC640',
            'ataduras' => '#52B6AC',
        ][$slug] ?? null;
    }

    protected function catalogSubfamilyAccentColorFor(string $slug): ?string
    {
        return [
            'elementos-de-cadena' => '#8BCFDE',
            'componentes-hilo-guardia-pat' => '#A46DA9',
            'accesorios-montajes-aisladores' => '#E85556',
            'montajes-de-aisladores' => '#E85556',
            'crucetas-brazos-liras-accesorios-fijacion' => '#FBC640',
            'crucetas-brazos-liras-y-accesorios-de-fijacion' => '#FBC640',
            'preensamblado' => '#52B6AC',
            'rienda' => '#E86428',
            'red-compacta' => '#9DC66C',
            'preformado' => '#E75A9A',
        ][$slug] ?? null;
    }
}
