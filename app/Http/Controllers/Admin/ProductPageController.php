<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\ProductSubfamily;
use App\Models\SiteSection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPageController extends Controller
{
    public function index(Request $request): Response
    {
        $hero = SiteSection::query()
            ->with('media')
            ->firstOrCreate(
                [
                    'page_key' => 'productos',
                    'section_key' => 'hero',
                ],
                [
                    'title' => 'PRODUCTOS',
                    'sort_order' => 'A',
                    'is_active' => true,
                ]
            );

        return Inertia::render('Admin/Products/Index', [
            'hero' => [
                'id' => $hero->id,
                'title' => $hero->title,
                'media_id' => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'is_active' => $hero->is_active,
                'sort_order' => $hero->sort_order,
            ],
            'families' => ProductFamily::query()
                ->with(['coverMedia', 'bannerMedia'])
                ->withCount(['subfamilies', 'products'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ProductFamily $family) => [
                    'id' => $family->id,
                    'name' => $family->name,
                    'slug' => $family->slug,
                    'description' => $family->description,
                    'cover_media_id' => $family->cover_media_id,
                    'cover_media_url' => media_asset_url($family->coverMedia),
                    'banner_media_id' => $family->banner_media_id,
                    'banner_media_url' => media_asset_url($family->bannerMedia),
                    'accent_color' => $family->accent_color,
                    'sort_order' => $family->sort_order,
                    'is_active' => $family->is_active,
                    'show_on_products_page' => $family->show_on_products_page,
                    'show_on_home' => $family->show_on_home,
                    'subfamilies_count' => $family->subfamilies_count,
                    'products_count' => $family->products_count,
                ])
                ->values(),
            'subfamilies' => ProductSubfamily::query()
                ->with('family')
                ->withCount('products')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ProductSubfamily $subfamily) => [
                    'id' => $subfamily->id,
                    'product_family_id' => $subfamily->product_family_id,
                    'family_name' => $subfamily->family?->name,
                    'name' => $subfamily->name,
                    'slug' => $subfamily->slug,
                    'short_description' => $subfamily->short_description,
                    'description' => $subfamily->description,
                    'accent_color' => $subfamily->accent_color,
                    'sort_order' => $subfamily->sort_order,
                    'is_active' => $subfamily->is_active,
                    'show_on_home' => $subfamily->show_on_home,
                    'show_on_family_page' => $subfamily->show_on_family_page,
                    'products_count' => $subfamily->products_count,
                ])
                ->values(),
            'products' => Product::query()
                ->with(['family', 'subfamily', 'mainMedia'])
                ->withCount(['media', 'specTables', 'relatedProducts'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'product_family_id' => $product->product_family_id,
                    'product_subfamily_id' => $product->product_subfamily_id,
                    'family_name' => $product->family?->name,
                    'subfamily_name' => $product->subfamily?->name,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'short_description' => $product->short_description,
                    'material' => $product->material,
                    'treatment' => $product->treatment,
                    'observations' => $product->observations,
                    'main_media_id' => $product->main_media_id,
                    'main_media_url' => media_asset_url($product->mainMedia),
                    'sort_order' => $product->sort_order,
                    'is_active' => $product->is_active,
                    'is_featured_home' => $product->is_featured_home,
                    'is_featured_family' => $product->is_featured_family,
                    'gallery_count' => $product->media_count,
                    'spec_tables_count' => $product->spec_tables_count,
                    'related_count' => $product->related_products_count,
                ])
                ->values(),
            'stats' => [
                'families' => ProductFamily::query()->count(),
                'subfamilies' => ProductSubfamily::query()->count(),
                'products' => Product::query()->count(),
            ],
            'initialTab' => in_array($request->string('tab')->toString(), ['cover', 'families', 'subfamilies', 'products', 'import'], true)
                ? $request->string('tab')->toString()
                : 'products',
            'publicProductsUrl' => route('web.products.index'),
            'importTemplateUrl' => route('admin.products.template'),
        ]);
    }
}
