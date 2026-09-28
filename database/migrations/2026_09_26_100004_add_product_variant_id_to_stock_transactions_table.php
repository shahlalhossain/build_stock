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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()
                ->constrained('product_variants')->nullOnDelete();
        });

        // Only Present on Databases where this Column Predates the Variant
        // Refactor — a Fresh Migrate never creates it.
        if (Schema::hasColumn('stock_transactions', 'product_attribute_value_id')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_attribute_value_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')->nullable()->after('product_id')
                ->constrained('product_attribute_values');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
