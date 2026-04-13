<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 20);
            $table->string('disk')->default('public');
            $table->string('path')->unique();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });

        Schema::create('footer_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('brand_name')->default('Tarea SRL');
            $table->string('newsletter_title')->nullable();
            $table->string('newsletter_placeholder')->nullable();
            $table->string('contact_title')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('email_secondary')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->text('copyright_text')->nullable();
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('platform');
            $table->string('label')->nullable();
            $table->string('url');
            $table->string('location')->default('footer');
            $table->string('icon')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('site_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('page_key');
            $table->string('section_key');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('secondary_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->json('meta_json')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['page_key', 'section_key']);
            $table->index(['page_key', 'sort_order']);
        });

        Schema::create('section_field_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_section_id')->constrained('site_sections')->cascadeOnDelete();
            $table->string('field_key');
            $table->string('field_label')->nullable();
            $table->string('field_type')->default('text');
            $table->longText('field_value')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_section_id', 'field_key']);
        });

        Schema::create('section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_section_id')->constrained('site_sections')->cascadeOnDelete();
            $table->string('item_key')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('link_url')->nullable();
            $table->string('accent_color')->nullable();
            $table->json('meta_json')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['site_section_id', 'sort_order']);
        });

        Schema::create('home_hero_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('media_type', 20)->default('video');
            $table->foreignId('desktop_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('alt_text')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->unsignedInteger('autoplay_override_seconds')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_slides');
        Schema::dropIfExists('section_items');
        Schema::dropIfExists('section_field_values');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('footer_settings');
        Schema::dropIfExists('media_assets');
    }
};
