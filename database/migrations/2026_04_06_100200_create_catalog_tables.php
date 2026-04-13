<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_families', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('banner_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('accent_color')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_products_page')->default(true);
            $table->boolean('show_on_home')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('product_subfamilies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_family_id')->constrained('product_families')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_family_id', 'slug']);
            $table->index(['product_family_id', 'sort_order']);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_family_id')->constrained('product_families')->cascadeOnDelete();
            $table->foreignId('product_subfamily_id')->nullable()->constrained('product_subfamilies')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('main_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('technical_sheet_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured_home')->default(false);
            $table->boolean('is_featured_family')->default(false);
            $table->timestamps();

            $table->index(['product_family_id', 'product_subfamily_id']);
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('product_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'media_id'], 'prod_media_unique');
        });

        Schema::create('product_spec_tables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();
        });

        Schema::create('product_spec_columns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_spec_table_id')->constrained('product_spec_tables')->cascadeOnDelete();
            $table->string('label');
            $table->string('unit')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();
        });

        Schema::create('product_spec_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_spec_table_id')->constrained('product_spec_tables')->cascadeOnDelete();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();
        });

        Schema::create('product_spec_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_spec_row_id')->constrained('product_spec_rows')->cascadeOnDelete();
            $table->foreignId('product_spec_column_id')->constrained('product_spec_columns')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['product_spec_row_id', 'product_spec_column_id'], 'prod_spec_value_unique');
        });

        Schema::create('product_related', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id'], 'prod_related_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related');
        Schema::dropIfExists('product_spec_values');
        Schema::dropIfExists('product_spec_rows');
        Schema::dropIfExists('product_spec_columns');
        Schema::dropIfExists('product_spec_tables');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_subfamilies');
        Schema::dropIfExists('product_families');
    }
};
