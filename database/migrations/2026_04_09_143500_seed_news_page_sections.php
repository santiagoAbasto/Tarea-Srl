<?php

use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSection::updateOrCreate(
            [
                'page_key' => 'novedades',
                'section_key' => 'hero',
            ],
            [
                'title' => 'NOVEDADES',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        SiteSection::query()
            ->where('page_key', 'novedades')
            ->where('section_key', 'hero')
            ->delete();
    }
};
