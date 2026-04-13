<?php

use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSection::query()->updateOrCreate(
            ['page_key' => 'productos', 'section_key' => 'hero'],
            [
                'title' => 'PRODUCTOS',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        SiteSection::query()
            ->where('page_key', 'productos')
            ->where('section_key', 'hero')
            ->delete();
    }
};
