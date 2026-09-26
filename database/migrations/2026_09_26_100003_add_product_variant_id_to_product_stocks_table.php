<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique('product_stocks_product_variant_store_unique');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_attribute_value_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'product_variant_id', 'store_id'], 'product_stocks_product_variant_store_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropUnique('product_stocks_product_variant_store_unique');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')->nullable()->after('product_id')
                ->constrained('product_attribute_values');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'product_attribute_value_id', 'store_id'], 'product_stocks_product_variant_store_unique');
        });
    }
};
