<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * product_variants was dropped (2026_09_24_120001), which already
     * dropped both tables' product_variant_id foreign keys at that time
     * (confirmed via information_schema — neither FK exists anymore, only
     * product_stocks' unique index on [product_variant_id, store_id]
     * remains). Both tables are confirmed empty, so this is a pure
     * structural swap with no data to carry across.
     */
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique(['product_variant_id', 'store_id']);
            $table->dropColumn('product_variant_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_id')->after('id')->constrained('products')->cascadeOnDelete();
            $table->unique(['product_id', 'store_id']);
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropColumn('product_variant_id');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreignId('product_id')->after('stock_transaction_id')->constrained('products');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->after('stock_transaction_id')->constrained('product_variants');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'store_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->after('id')->constrained('product_variants')->cascadeOnDelete();
            $table->unique(['product_variant_id', 'store_id']);
        });
    }
};
