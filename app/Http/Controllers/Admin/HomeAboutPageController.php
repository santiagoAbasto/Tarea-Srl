<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class HomeAboutPageController extends Controller
{
    public function index(): Response
    {
        $section = SiteSection::query()
            ->with(['media'])
            ->firstOrCreate(
                [
                    'page_key' => 'home',
                    'section_key' => 'about_preview',
                ],
                [
                    'title' => 'Quienes somos',
                    'description' => '',
                    'button_text' => 'Mas info',
                    'button_url' => '/nosotros',
                    'sort_order' => 'C',
                    'is_active' => true,
                ],
            );

        return Inertia::render('Admin/HomeAbout/Index', [
            'section' => [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'button_text' => $section->button_text,
                'button_url' => $section->button_url,
                'media_id' => $section->media_id,
                'media_url' => media_asset_url($section->media),
                'is_active' => $section->is_active,
                'sort_order' => $section->sort_order,
            ],
            'publicHomeUrl' => route('web.home'),
        ]);
    }
}
