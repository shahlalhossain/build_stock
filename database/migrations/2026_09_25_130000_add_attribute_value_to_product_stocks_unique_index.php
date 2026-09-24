<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Variant Product needs one independent product_stocks row PER Variant PER Store,
     * not one shared row per Product+Store. NULL product_attribute_value_id (non-variant
     * Products) is treated as distinct per row by MySQL, so this does not enforce
     * singularity for non-variant Products — that is guarded in application code instead.
     */
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // product_id's Foreign Key rides on this Unique Index — give it its own
            // plain Index first so the Unique Index can be safely dropped/replaced.
            $table->index('product_id', 'product_stocks_product_id_index');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique('product_stocks_product_id_store_id_unique');
            $table->unique(['product_id', 'product_attribute_value_id', 'store_id'], 'product_stocks_product_variant_store_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique('product_stocks_product_variant_store_unique');
            $table->unique(['product_id', 'store_id'], 'product_stocks_product_id_store_id_unique');
            $table->dropIndex('product_stocks_product_id_index');
        });
    }
};
