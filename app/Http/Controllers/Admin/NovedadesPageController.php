<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class NovedadesPageController extends Controller
{
    public function index(): Response
    {
        $allowedTabs = ['page', 'categorias', 'novedades'];
        $initialTab  = in_array(request()->string('tab')->toString(), $allowedTabs, true)
            ? request()->string('tab')->toString()
            : 'page';

        $hero = SiteSection::query()
            ->where('page_key', 'novedades')
            ->whereIn('section_key', ['hero', 'novedades_banner'])
            ->orderByRaw("case when section_key = 'hero' then 0 else 1 end")
            ->first();

        if (! $hero) {
            $hero = SiteSection::query()->create([
                'page_key' => 'novedades',
                'section_key' => 'novedades_banner',
                'title' => 'NOVEDADES',
                'sort_order' => 'A',
                'is_active' => true,
            ]);
        }

        $hero->load('media');

        $categories = PostCategory::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PostCategory $cat): array => [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'slug'        => $cat->slug,
                'color'       => $cat->color,
                'sort_order'  => $cat->sort_order,
                'is_active'   => $cat->is_active,
                'posts_count' => $cat->posts_count,
            ])
            ->values();

        $posts = Post::query()
            ->with(['category', 'coverMedia'])
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Post $post): array => [
                'id'               => $post->id,
                'title'            => $post->title,
                'slug'             => $post->slug,
                'excerpt'          => $post->excerpt,
                'author_name'      => $post->author_name,
                'published_at'     => $post->published_at?->format('Y-m-d'),
                'sort_order'       => $post->sort_order,
                'is_active'        => $post->is_active,
                'show_on_home'     => $post->show_on_home,
                'is_featured'      => $post->is_featured,
                'cover_media_id'   => $post->cover_media_id,
                'cover_url'        => media_asset_url($post->coverMedia),
                'category_id'      => $post->post_category_id,
                'category_name'    => $post->category?->name,
                'category_color'   => $post->category?->color,
            ])
            ->values();

        return Inertia::render('Admin/Novedades/Index', [
            'hero' => [
                'id' => $hero->id,
                'section_key' => $hero->section_key,
                'title' => $hero->title,
                'media_id' => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'sort_order' => $hero->sort_order,
                'is_active' => $hero->is_active,
            ],
            'categories'    => $categories,
            'posts'         => $posts,
            'stats' => [
                'total'      => Post::query()->count(),
                'active'     => Post::query()->where('is_active', true)->count(),
                'featured'   => Post::query()->where('is_featured', true)->count(),
                'categories' => PostCategory::query()->count(),
            ],
            'initialTab'       => $initialTab,
            'publicNovedadesUrl' => route('web.news.index'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Novedades/Create', [
            'categories' => PostCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'color']),
        ]);
    }

    public function edit(int $id): Response
    {
        $post = Post::query()->with(['category', 'coverMedia'])->findOrFail($id);

        return Inertia::render('Admin/Novedades/Edit', [
            'post' => [
                'id'               => $post->id,
                'title'            => $post->title,
                'slug'             => $post->slug,
                'excerpt'          => $post->excerpt ?? '',
                'content'          => $post->content ?? '',
                'author_name'      => $post->author_name ?? '',
                'published_at'     => $post->published_at?->format('Y-m-d') ?? '',
                'sort_order'       => $post->sort_order ?? 'A',
                'is_active'        => $post->is_active,
                'show_on_home'     => $post->show_on_home,
                'is_featured'      => $post->is_featured,
                'post_category_id' => $post->post_category_id,
                'cover_media_id'   => $post->cover_media_id,
                'cover_url'        => media_asset_url($post->coverMedia),
                'seo_title'        => $post->seo_title ?? '',
                'seo_description'  => $post->seo_description ?? '',
            ],
            'categories' => PostCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'color']),
        ]);
    }
}
