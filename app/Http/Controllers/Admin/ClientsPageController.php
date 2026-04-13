<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class ClientsPageController extends Controller
{
    public function index(): Response
    {
        $hero = SiteSection::query()
            ->where('page_key', 'clientes')
            ->whereIn('section_key', ['hero', 'clientes_banner'])
            ->orderByRaw("case when section_key = 'hero' then 0 else 1 end")
            ->first();

        if (! $hero) {
            $hero = SiteSection::query()->create([
                'page_key' => 'clientes',
                'section_key' => 'clientes_banner',
                'title' => 'CLIENTES',
                'sort_order' => 'A',
                'is_active' => true,
            ]);
        }

        $hero->load('media');

        $content = SiteSection::query()->firstOrCreate(
            [
                'page_key' => 'clientes',
                'section_key' => 'clientes_content',
            ],
            [
                'title' => 'Nuestros clientes',
                'sort_order' => 'B',
                'is_active' => true,
            ],
        );

        return Inertia::render('Admin/Clients/Index', [
            'hero' => [
                'id' => $hero->id,
                'section_key' => $hero->section_key,
                'title' => $hero->title,
                'media_id' => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'sort_order' => $hero->sort_order,
                'is_active' => $hero->is_active,
            ],
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'sort_order' => $content->sort_order,
                'is_active' => $content->is_active,
            ],
            'categories' => ClientCategory::query()
                ->withCount('clients')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ClientCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'sort_order' => $category->sort_order,
                    'is_active' => $category->is_active,
                    'clients_count' => $category->clients_count,
                ])
                ->values(),
            'clients' => Client::query()
                ->with(['categories', 'logo'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Client $client): array => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'logo_media_id' => $client->logo_media_id,
                    'logo_url' => media_asset_url($client->logo),
                    'website_url' => $client->website_url,
                    'sort_order' => $client->sort_order,
                    'is_active' => $client->is_active,
                    'show_on_home' => $client->show_on_home,
                    'client_category_id' => $client->client_category_id,
                    'client_category_ids' => $client->categories->pluck('id')->values(),
                    'category_names' => $client->categories->pluck('name')->values(),
                ])
                ->values(),
            'stats' => [
                'categories' => ClientCategory::query()->count(),
                'clients' => Client::query()->count(),
                'home_clients' => Client::query()->where('show_on_home', true)->where('is_active', true)->count(),
            ],
            'initialTab' => in_array(request()->string('tab')->toString(), ['page', 'categories', 'clients'], true)
                ? request()->string('tab')->toString()
                : 'page',
            'publicClientsUrl' => route('web.clients.index'),
        ]);
    }
}
