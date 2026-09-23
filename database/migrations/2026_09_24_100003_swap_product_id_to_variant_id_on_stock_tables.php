<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Both tables are empty at the time this migration is written (confirmed
     * via tinker before drafting) — this is a pure structural swap, no data
     * to carry across.
     */
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropUnique(['product_id', 'store_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->after('id')->constrained('product_variants')->cascadeOnDelete();
            $table->unique(['product_variant_id', 'store_id']);
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->after('stock_transaction_id')->constrained('product_variants');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreignId('product_id')->after('stock_transaction_id')->constrained('products');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropUnique(['product_variant_id', 'store_id']);
            $table->dropColumn('product_variant_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_id')->after('id')->constrained('products')->cascadeOnDelete();
            $table->unique(['product_id', 'store_id']);
        });
    }
};
