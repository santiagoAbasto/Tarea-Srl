<?php

use App\Models\ProductFamily;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ProductFamily::query()
            ->whereIn('slug', [
                'elementos-pat',
                'cables',
                'ataduras',
            ])
            ->update(['show_on_home' => false]);
    }

    public function down(): void
    {
        //
    }
};
