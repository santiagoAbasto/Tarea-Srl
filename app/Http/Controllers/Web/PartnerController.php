<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\Partner;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        $sections = SiteSection::query()
            ->with('media')
            ->where('page_key', 'representadas')
            ->where('is_active', true)
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.partners.index', [
            'hero' => $sections->get('hero') ?? $sections->get('representadas_banner'),
            'partners' => Partner::query()->with('logo')->where('partner_type', 'representada')->where('is_active', true)->where('show_on_page', true)->orderBy('sort_order')->get(),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
