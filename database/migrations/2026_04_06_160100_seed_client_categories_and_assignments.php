<?php

use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'energia' => ['name' => 'Energía', 'sort_order' => SortOrder::fromPosition(1)],
            'petroleo' => ['name' => 'Petróleo', 'sort_order' => SortOrder::fromPosition(2)],
            'mineria' => ['name' => 'Minería', 'sort_order' => SortOrder::fromPosition(3)],
        ];

        $categoryIds = [];

        foreach ($categories as $slug => $category) {
            $existing = DB::table('client_categories')->where('slug', $slug)->first();

            if ($existing) {
                DB::table('client_categories')->where('id', $existing->id)->update([
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

                $categoryIds[$slug] = $existing->id;
            } else {
                $categoryIds[$slug] = DB::table('client_categories')->insertGetId([
                    'name' => $category['name'],
                    'slug' => $slug,
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $clients = [
            ['slug' => 'epe', 'name' => 'EPE', 'path' => 'uploads/clients/epe.png', 'sort' => 'A', 'home' => true],
            ['slug' => 'energia-san-juan', 'name' => 'Energía San Juan', 'path' => 'uploads/clients/energia-san-juan.png', 'sort' => 'AA', 'home' => true],
            ['slug' => 'transener', 'name' => 'Transener', 'path' => 'uploads/clients/transener.png', 'sort' => 'AB', 'home' => true],
            ['slug' => 'edea', 'name' => 'Edea', 'path' => 'uploads/clients/edea.png', 'sort' => 'AC', 'home' => true],
            ['slug' => 'eden', 'name' => 'EDEN', 'path' => 'uploads/clients/eden.png', 'sort' => 'AD', 'home' => true],
            ['slug' => 'edenor', 'name' => 'Edenor', 'path' => 'uploads/clients/edenor.png', 'sort' => 'AE', 'home' => true],
            ['slug' => 'edesur', 'name' => 'EDESUR', 'path' => 'uploads/clients/edesur.png', 'sort' => 'AF', 'home' => false],
            ['slug' => 'pan-american-energy', 'name' => 'Pan American Energy', 'path' => 'uploads/clients/pan-american-energy.png', 'sort' => 'AG', 'home' => false],
            ['slug' => 'ypf', 'name' => 'YPF', 'path' => 'uploads/clients/ypf.png', 'sort' => 'AH', 'home' => false],
            ['slug' => 'tecpetrol', 'name' => 'Tecpetrol', 'path' => 'uploads/clients/tecpetrol.png', 'sort' => 'AI', 'home' => false],
            ['slug' => 'cgc', 'name' => 'CGC', 'path' => 'uploads/clients/cgc.png', 'sort' => 'AJ', 'home' => false],
        ];

        foreach ($clients as $client) {
            $media = DB::table('media_assets')->where('path', $client['path'])->first();

            if (! $media) {
                $mediaId = DB::table('media_assets')->insertGetId([
                    'type' => 'image',
                    'disk' => 'public',
                    'path' => $client['path'],
                    'title' => $client['name'],
                    'alt_text' => $client['name'],
                    'mime_type' => 'image/png',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $mediaId = $media->id;
            }

            $existingClient = DB::table('clients')->where('slug', $client['slug'])->first();

            if ($existingClient) {
                DB::table('clients')->where('id', $existingClient->id)->update([
                    'name' => $client['name'],
                    'logo_media_id' => $mediaId,
                    'sort_order' => $client['sort'],
                    'is_active' => true,
                    'show_on_home' => $client['home'],
                    'updated_at' => now(),
                ]);

                $clientId = $existingClient->id;
            } else {
                $clientId = DB::table('clients')->insertGetId([
                    'client_category_id' => $categoryIds['energia'],
                    'name' => $client['name'],
                    'slug' => $client['slug'],
                    'logo_media_id' => $mediaId,
                    'website_url' => null,
                    'sort_order' => $client['sort'],
                    'is_active' => true,
                    'show_on_home' => $client['home'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (array_values($categoryIds) as $index => $categoryId) {
                DB::table('client_category_client')->updateOrInsert(
                    [
                        'client_id' => $clientId,
                        'client_category_id' => $categoryId,
                    ],
                    [
                        'sort_order' => SortOrder::fromPosition($index + 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('client_category_client')
            ->whereIn('client_id', DB::table('clients')->whereIn('slug', [
                'epe',
                'energia-san-juan',
                'transener',
                'edea',
                'eden',
                'edenor',
                'edesur',
                'pan-american-energy',
                'ypf',
                'tecpetrol',
                'cgc',
            ])->pluck('id'))
            ->delete();
    }
};
