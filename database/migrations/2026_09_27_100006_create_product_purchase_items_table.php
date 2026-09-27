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
        Schema::create('product_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_purchase_id')->constrained('product_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');

            // The Unit this Line was Purchased in — may differ from the Product's own
            // base Unit and/or from the linked Requisition line's Unit.
            $table->foreignId('unit_id')->constrained('product_units');

            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('line_total', 15, 2)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchase_items');
    }
};
