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
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();

            $table->integer('product_id');
            $table->integer('unit_id');

            // Multiply a Quantity in unit_id by this Factor to get the equivalent
            // Quantity in the Product's own base unit_id (e.g. 1 Bag = 50 Kg -> 50.000000).
            $table->decimal('factor_to_base', 15, 6);

            $table->boolean('is_active')->default(true);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();

            $table->unique(['product_id', 'unit_id'], 'unit_conversions_product_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
