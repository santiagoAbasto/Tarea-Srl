<?php

use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSection::updateOrCreate(
            [
                'page_key' => 'contacto',
                'section_key' => 'hero',
            ],
            [
                'title' => 'CONTACTO',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        SiteSection::query()
            ->where('page_key', 'contacto')
            ->where('section_key', 'hero')
            ->delete();
    }
};
