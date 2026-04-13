<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function show(): View
    {
        $sections = SiteSection::query()->with(['media', 'secondaryMedia', 'items.media', 'fieldValues'])
            ->where('page_key', 'nosotros')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.about.show', [
            'sections' => $sections,
            'hero' => $sections->get('hero'),
            'intro' => $sections->get('intro'),
            'missionVision' => $sections->get('mission_vision'),
            'values' => $sections->get('values'),
            'memberships' => $sections->get('memberships'),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
