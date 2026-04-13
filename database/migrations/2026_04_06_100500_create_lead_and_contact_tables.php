<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('catalog_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('contact_page_settings', function (Blueprint $table): void {
            $table->id();
            $table->text('address')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('email_secondary')->nullable();
            $table->longText('map_iframe')->nullable();
            $table->string('map_link')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('quote_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('quote_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->string('product_sku_snapshot')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('quote_request_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('file_name_snapshot')->nullable();
            $table->string('sort_order', 16)->default('A');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_attachments');
        Schema::dropIfExists('quote_request_items');
        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('contact_requests');
        Schema::dropIfExists('contact_page_settings');
        Schema::dropIfExists('catalog_requests');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
