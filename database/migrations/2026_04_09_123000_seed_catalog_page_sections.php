<?php

use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSection::query()->updateOrCreate(
            ['page_key' => 'catalogo', 'section_key' => 'catalogo_banner'],
            [
                'title' => 'CATÁLOGO',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'catalogo', 'section_key' => 'catalogo_content'],
            [
                'title' => 'Solicita nuestro catálogo completo',
                'sort_order' => 'B',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        //
    }
};
