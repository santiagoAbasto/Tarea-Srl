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
        $allowedTabs = ['categorias', 'novedades'];
        $initialTab  = in_array(request()->string('tab')->toString(), $allowedTabs, true)
            ? request()->string('tab')->toString()
            : 'novedades';

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
