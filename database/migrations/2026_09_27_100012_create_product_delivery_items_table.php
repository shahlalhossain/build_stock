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
        Schema::create('product_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_delivery_id')->constrained('product_deliveries')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');

            // The Unit this Line is Delivered in — normalized to the Product's
            // base Unit via UnitConversionService before touching product_stocks.
            $table->foreignId('unit_id')->constrained('product_units');

            $table->decimal('quantity', 15, 2);
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_delivery_items');
    }
};
