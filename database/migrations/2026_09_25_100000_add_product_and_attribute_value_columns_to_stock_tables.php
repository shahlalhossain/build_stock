<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All three tables are confirmed empty, so product_id/product_attribute_value_id
     * can be added as NOT NULL directly, no backfill needed.
     */
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('product_id')->after('code')->constrained('products');
            $table->foreignId('product_attribute_value_id')->after('product_id')->constrained('product_attribute_values');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')->after('product_id')->constrained('product_attribute_values');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')->after('product_id')->constrained('product_attribute_values');
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['product_attribute_value_id']);
            $table->dropColumn('product_attribute_value_id');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_attribute_value_id']);
            $table->dropColumn('product_attribute_value_id');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['product_attribute_value_id']);
            $table->dropColumn('product_attribute_value_id');
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
