<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeHeroSlide;
use Inertia\Inertia;
use Inertia\Response;

class HomeHeroSlidePageController extends Controller
{
    public function index(): Response
    {
        $slides = HomeHeroSlide::query()
            ->with(['desktopMedia', 'mobileMedia'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (HomeHeroSlide $slide): array => [
                'id' => $slide->id,
                'title' => $slide->title,
                'subtitle' => $slide->subtitle,
                'description' => $slide->description,
                'button_text' => $slide->button_text,
                'button_url' => $slide->button_url,
                'media_type' => $slide->media_type,
                'desktop_media_id' => $slide->desktop_media_id,
                'mobile_media_id' => $slide->mobile_media_id,
                'desktop_media_url' => media_asset_url($slide->desktopMedia),
                'mobile_media_url' => media_asset_url($slide->mobileMedia),
                'desktop_media_path' => $slide->desktopMedia?->path,
                'mobile_media_path' => $slide->mobileMedia?->path,
                'desktop_youtube_embed_url' => $slide->desktopYoutubeEmbedUrl(),
                'mobile_youtube_embed_url' => $slide->mobileYoutubeEmbedUrl(),
                'alt_text' => $slide->alt_text,
                'sort_order' => $slide->sort_order,
                'autoplay_override_seconds' => $slide->autoplay_override_seconds,
                'is_active' => $slide->is_active,
                'updated_at' => optional($slide->updated_at)?->toIso8601String(),
            ])
            ->values();

        return Inertia::render('Admin/HomeHeroSlides/Index', [
            'slides' => $slides,
            'publicHomeUrl' => route('web.home'),
        ]);
    }
}
