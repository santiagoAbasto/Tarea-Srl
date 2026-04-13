<?php

use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_category_client', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('client_category_id')->constrained('client_categories')->cascadeOnDelete();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();

            $table->unique(['client_id', 'client_category_id'], 'client_category_unique');
            $table->index(['client_category_id', 'sort_order']);
        });

        $clients = DB::table('clients')
            ->select('id', 'client_category_id', 'sort_order')
            ->whereNotNull('client_category_id')
            ->get();

        foreach ($clients as $client) {
            DB::table('client_category_client')->updateOrInsert(
                [
                    'client_id' => $client->id,
                    'client_category_id' => $client->client_category_id,
                ],
                [
                    'sort_order' => SortOrder::normalize($client->sort_order ?? 'A'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_category_client');
    }
};
