<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\View\View;

class QualityController extends Controller
{
    public function show(): View
    {
        $sections = SiteSection::query()
            ->with(['media', 'secondaryMedia', 'items.media', 'fieldValues'])
            ->where('page_key', 'calidad')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        $logos = $sections->get('quality_logos');

        if ($logos) {
            $logos->setAttribute('title', '');
        }

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.quality.show', [
            'sections' => $sections,
            'hero' => $sections->get('quality_banner'),
            'policy' => $sections->get('quality_policy'),
            'logos' => $logos,
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
