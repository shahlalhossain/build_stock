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
        Schema::create('product_receive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_receive_id')->constrained('product_receives')->cascadeOnDelete();

            // Links back to the specific Transfer Line this Row is Receiving
            // against — needed to compute Remaining-to-Receive per Transfer Line
            // (summed across every APPROVED Receive Item against it).
            $table->foreignId('transfer_item_id')->constrained('product_transfer_items');

            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');

            // The Unit this Line was actually Received in — may differ from the
            // Transfer Line's own Unit (destination Store may count differently).
            $table->foreignId('unit_id')->constrained('product_units');

            $table->decimal('received_quantity', 15, 2);
            $table->text('variance_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_receive_items');
    }
};
