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
        Schema::table('product_purchase_items', function (Blueprint $table) {
            // Optional Link back to the Requisition Line this Purchase Line Fulfills —
            // enables Partial, Multi-Purchase Fulfillment of a single Requisition Item.
            $table->foreignId('requisition_item_id')->nullable()->after('product_purchase_id')
                ->constrained('product_requisition_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requisition_item_id');
        });
    }
};
