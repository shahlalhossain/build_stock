<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Variant setup is optional — most products have no variants at all, so
     * product_attribute_value_id must allow NULL (= plain, non-variant line/stock row).
     *
     * Raw SQL (not ->change()) because doctrine/dbal is not installed; MODIFY COLUMN
     * leaves the existing foreign key constraints on these columns untouched.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE stock_transactions MODIFY product_attribute_value_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE stock_transaction_items MODIFY product_attribute_value_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE product_stocks MODIFY product_attribute_value_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE product_stocks MODIFY product_attribute_value_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_transaction_items MODIFY product_attribute_value_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE stock_transactions MODIFY product_attribute_value_id BIGINT UNSIGNED NOT NULL');
    }
};
