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
        Schema::create('product_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_requisition_id')->constrained('product_requisitions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');

            // The Unit this Quantity is Requested in — may differ from the Product's
            // own base Unit; no Cost/Price concept at the Requisition stage.
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
        Schema::dropIfExists('product_requisition_items');
    }
};
