<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use App\Models\Post;
use App\Models\SocialLink;
use App\Models\SiteSection;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $hero = SiteSection::query()
            ->with('media')
            ->where('page_key', 'novedades')
            ->where('section_key', 'hero')
            ->first();

        $posts = Post::query()
                ->with(['category', 'coverMedia'])
                ->where('is_active', true)
                ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($sub) => $sub->where('slug', $request->string('category'))))
                ->latest('published_at')
                ->paginate(max(1, min(100, $request->integer('per_page', 9))))
                ->withQueryString();

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.news.index', [
            'hero' => $hero,
            'posts' => $posts,
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()->with(['category', 'coverMedia'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $hero = SiteSection::query()
            ->with('media')
            ->where('page_key', 'novedades')
            ->where('section_key', 'hero')
            ->first();

        $related = Post::query()
            ->with(['category', 'coverMedia'])
            ->where('is_active', true)
            ->where('id', '!=', $post->id)
            ->when($post->post_category_id, fn ($query) => $query->where('post_category_id', $post->post_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        if ($related->count() < 3) {
            $missing = 3 - $related->count();

            $fallback = Post::query()
                ->with(['category', 'coverMedia'])
                ->where('is_active', true)
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->latest('published_at')
                ->limit($missing)
                ->get();

            $related = $related->concat($fallback);
        }

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.news.show', [
            'hero' => $hero,
            'post' => $post,
            'related' => $related,
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
