<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transaction_id')->constrained('stock_transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');

            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable(); // Relevant for purchase / opening_balance
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_items');
    }
};
