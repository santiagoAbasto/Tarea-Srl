<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class RepresentadasPageController extends Controller
{
    public function index(): Response
    {
        $hero = SiteSection::query()
            ->where('page_key', 'representadas')
            ->whereIn('section_key', ['hero', 'representadas_banner'])
            ->orderByRaw("case when section_key = 'hero' then 0 else 1 end")
            ->first();

        if (! $hero) {
            $hero = SiteSection::query()->create([
                'page_key'    => 'representadas',
                'section_key' => 'representadas_banner',
                'title'       => 'REPRESENTADAS',
                'sort_order'  => 'A',
                'is_active'   => true,
            ]);
        }

        $hero->load('media');

        $partners = Partner::query()
            ->with('logo')
            ->where('partner_type', 'representada')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Partner $partner): array => [
                'id'           => $partner->id,
                'name'         => $partner->name,
                'slug'         => $partner->slug,
                'logo_media_id' => $partner->logo_media_id,
                'logo_url'     => media_asset_url($partner->logo),
                'website_url'  => $partner->website_url,
                'sort_order'   => $partner->sort_order,
                'is_active'    => $partner->is_active,
                'show_on_home' => $partner->show_on_home,
                'show_on_page' => $partner->show_on_page,
            ])
            ->values();

        return Inertia::render('Admin/Representadas/Index', [
            'hero' => [
                'id'          => $hero->id,
                'section_key' => $hero->section_key,
                'title'       => $hero->title,
                'media_id'    => $hero->media_id,
                'media_url'   => media_asset_url($hero->media),
                'sort_order'  => $hero->sort_order,
                'is_active'   => $hero->is_active,
            ],
            'partners' => $partners,
            'stats' => [
                'total'     => Partner::query()->where('partner_type', 'representada')->count(),
                'active'    => Partner::query()->where('partner_type', 'representada')->where('is_active', true)->count(),
                'on_home'   => Partner::query()->where('partner_type', 'representada')->where('show_on_home', true)->where('is_active', true)->count(),
                'on_page'   => Partner::query()->where('partner_type', 'representada')->where('show_on_page', true)->where('is_active', true)->count(),
            ],
            'initialTab' => in_array(request()->string('tab')->toString(), ['page', 'representadas'], true)
                ? request()->string('tab')->toString()
                : 'page',
            'publicRepresentadasUrl' => route('web.partners.index'),
        ]);
    }
}
