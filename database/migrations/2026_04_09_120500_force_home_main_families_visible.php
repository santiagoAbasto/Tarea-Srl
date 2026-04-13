<?php

use App\Models\ProductFamily;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ProductFamily::query()
            ->whereIn('slug', [
                'seccionadores-y-descargadores',
                'aisladores',
            ])
            ->update([
                'show_on_home' => true,
                'is_active' => true,
            ]);
    }

    public function down(): void
    {
        //
    }
};
