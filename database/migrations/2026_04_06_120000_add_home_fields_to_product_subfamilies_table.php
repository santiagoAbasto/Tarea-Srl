<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_subfamilies', function (Blueprint $table): void {
            $table->string('short_description')->nullable()->after('name');
            $table->foreignId('cover_media_id')->nullable()->after('description')->constrained('media_assets')->nullOnDelete();
            $table->boolean('show_on_home')->default(false)->after('is_active');
            $table->boolean('show_on_family_page')->default(true)->after('show_on_home');
        });
    }

    public function down(): void
    {
        Schema::table('product_subfamilies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cover_media_id');
            $table->dropColumn(['short_description', 'show_on_home', 'show_on_family_page']);
        });
    }
};
