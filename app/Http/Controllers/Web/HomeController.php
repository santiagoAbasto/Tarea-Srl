<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FooterSetting;
use App\Models\HomeHeroSlide;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sections = SiteSection::query()
            ->with(['media', 'secondaryMedia', 'items.media', 'fieldValues'])
            ->where('page_key', 'home')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        return view('web.home', [
            'slides' => HomeHeroSlide::query()->with(['desktopMedia', 'mobileMedia'])->where('is_active', true)->orderBy('sort_order')->get(),
            'sections' => $sections,
            'product_families' => ProductFamily::query()
                ->with(['coverMedia'])
                ->where('show_on_home', true)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'featured_products' => Product::query()->with(['family', 'subfamily', 'mainMedia', 'brandLogoMedia'])->where('is_featured_home', true)->where('is_active', true)->orderBy('sort_order')->get(),
            'posts' => Post::query()->with(['category', 'coverMedia'])->where('show_on_home', true)->where('is_active', true)->latest('published_at')->limit(6)->get(),
            'partners' => Partner::query()->with('logo')->where('show_on_home', true)->where('is_active', true)->orderBy('sort_order')->get(),
            'clients' => Client::query()->with(['category', 'logo'])->where('show_on_home', true)->where('is_active', true)->orderBy('sort_order')->get(),
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
