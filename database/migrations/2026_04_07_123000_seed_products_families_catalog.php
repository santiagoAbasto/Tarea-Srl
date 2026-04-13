<?php

use App\Models\ProductFamily;
use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $families = [
            ['slug' => 'herrajes-y-morseteria', 'name' => 'Herrajes y morseteria', 'accent_color' => '#173F73', 'sort_order' => 'A'],
            ['slug' => 'seccionadores-y-descargadores', 'name' => 'Seccionadores y descargadores', 'accent_color' => '#8ED9E8', 'sort_order' => 'AA'],
            ['slug' => 'aisladores', 'name' => 'Aisladores', 'accent_color' => '#B06AC5', 'sort_order' => 'AB'],
            ['slug' => 'elementos-pat', 'name' => 'Elementos PAT', 'accent_color' => '#EF6364', 'sort_order' => 'AC'],
            ['slug' => 'cables', 'name' => 'Cables', 'accent_color' => '#F3C340', 'sort_order' => 'AD'],
            ['slug' => 'ataduras', 'name' => 'Ataduras', 'accent_color' => '#69C4BE', 'sort_order' => 'AE'],
        ];

        foreach ($families as $family) {
            ProductFamily::query()->updateOrCreate(
                ['slug' => $family['slug']],
                [
                    'name' => $family['name'],
                    'accent_color' => $family['accent_color'],
                    'sort_order' => $family['sort_order'],
                    'is_active' => true,
                    'show_on_products_page' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        ProductFamily::query()
            ->whereIn('slug', [
                'seccionadores-y-descargadores',
                'aisladores',
                'elementos-pat',
                'cables',
                'ataduras',
            ])
            ->delete();

        ProductFamily::query()
            ->where('slug', 'herrajes-y-morseteria')
            ->update([
                'name' => 'Herrajes y morsetería',
                'accent_color' => '#173F73',
                'sort_order' => SortOrder::fromPosition(1),
                'show_on_products_page' => true,
            ]);
    }
};
