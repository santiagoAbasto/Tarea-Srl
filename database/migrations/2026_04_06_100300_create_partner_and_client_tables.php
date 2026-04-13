<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('website_url')->nullable();
            $table->string('partner_type')->default('representada');
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_home')->default(false);
            $table->boolean('show_on_page')->default(true);
            $table->timestamps();

            $table->index(['partner_type', 'sort_order']);
        });

        Schema::create('client_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_category_id')->constrained('client_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('website_url')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_home')->default(false);
            $table->timestamps();

            $table->index(['client_category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_categories');
        Schema::dropIfExists('partners');
    }
};
