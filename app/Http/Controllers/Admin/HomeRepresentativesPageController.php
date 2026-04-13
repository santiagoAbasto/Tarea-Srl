<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class HomeRepresentativesPageController extends Controller
{
    public function index(): Response
    {
        $section = SiteSection::query()->firstOrCreate(
            [
                'page_key' => 'home',
                'section_key' => 'representantes',
            ],
            [
                'title' => 'Representantes exclusivos',
                'sort_order' => 'A',
                'is_active' => true,
            ],
        );

        $partners = Partner::query()
            ->with('logo')
            ->where('partner_type', 'representada')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Partner $partner): array => [
                'id' => $partner->id,
                'name' => $partner->name,
                'website_url' => $partner->website_url,
                'sort_order' => $partner->sort_order,
                'is_active' => $partner->is_active,
                'show_on_home' => $partner->show_on_home,
                'show_on_page' => $partner->show_on_page,
                'logo_media_id' => $partner->logo_media_id,
                'logo_url' => media_asset_url($partner->logo),
            ])
            ->values();

        return Inertia::render('Admin/HomeRepresentatives/Index', [
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'button_text' => $section->button_text,
                'button_url' => $section->button_url,
                'is_active' => $section->is_active,
                'sort_order' => $section->sort_order,
            ],
            'partners' => $partners,
            'publicHomeUrl' => route('web.home'),
        ]);
    }
}
