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
        // Header-level "primary Item" convenience reference — see
        // StockTransactionService::headerProductFields(): a Transaction with no
        // Line Items yields product_id = null, so this must stay nullable.
        if (! Schema::hasColumn('stock_transactions', 'product_id')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('code')->constrained('products')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
