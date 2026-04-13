<?php

use App\Models\Product;
use App\Models\ProductFamily;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ProductFamily::query()
            ->whereIn('slug', [
                'herrajes-y-morseteria',
                'seccionadores-y-descargadores',
                'aisladores',
                'elementos-pat',
                'cables',
                'ataduras',
            ])
            ->update(['show_on_home' => true]);

        Product::query()
            ->whereIn('slug', [
                'morsa-retencion-mr-0',
                'mensula-retencion-pkr20',
                'muerto-anclaje-ha',
                'brazo-l-5kv-35kv',
                'pernos-mn-41c-mn-41r',
                'soporte-antibalanceo',
                'morsa-retencion-rg-3-anillo',
                'morseto-peine',
            ])
            ->update(['is_featured_home' => true]);
    }

    public function down(): void
    {
        //
    }
};
