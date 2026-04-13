<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $sections = SiteSection::query()
            ->with('media')
            ->where('page_key', 'clientes')
            ->where('is_active', true)
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.clients.index', [
            'hero' => $sections->get('hero') ?? $sections->get('clientes_banner'),
            'contentSection' => $sections->get('clientes_content'),
            'categories' => ClientCategory::query()
                ->with(['clients' => fn ($query) => $query->with('logo')->where('is_active', true)->orderBy('sort_order')])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
            'homeClientsCount' => Client::query()->where('show_on_home', true)->where('is_active', true)->count(),
        ]);
    }
}
