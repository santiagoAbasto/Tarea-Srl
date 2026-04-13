<?php

use App\Support\SortOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'social_links',
            'site_sections',
            'section_field_values',
            'section_items',
            'home_hero_slides',
            'product_families',
            'product_subfamilies',
            'products',
            'product_media',
            'product_spec_tables',
            'product_spec_columns',
            'product_spec_rows',
            'product_related',
            'partners',
            'client_categories',
            'clients',
            'post_categories',
            'posts',
            'quote_request_attachments',
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
            $this->convertColumnToString($table);

            foreach ($rows as $index => $id) {
                DB::table($table)
                    ->where('id', $id)
                    ->update(['sort_order' => SortOrder::fromPosition($index + 1)]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'social_links',
            'site_sections',
            'section_field_values',
            'section_items',
            'home_hero_slides',
            'product_families',
            'product_subfamilies',
            'products',
            'product_media',
            'product_spec_tables',
            'product_spec_columns',
            'product_spec_rows',
            'product_related',
            'partners',
            'client_categories',
            'clients',
            'post_categories',
            'posts',
            'quote_request_attachments',
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
            $this->convertColumnToInteger($table);

            foreach ($rows as $index => $id) {
                DB::table($table)
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    protected function convertColumnToString(string $table): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(sprintf(
            "ALTER TABLE `%s` MODIFY `sort_order` VARCHAR(16) NOT NULL DEFAULT 'A'",
            $table
        ));
    }

    protected function convertColumnToInteger(string $table): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(sprintf(
            "ALTER TABLE `%s` MODIFY `sort_order` INT UNSIGNED NOT NULL DEFAULT 1",
            $table
        ));
    }
};
