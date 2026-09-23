<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // System Generated, e.g. STK-0001

            $table->enum('type', [
                'opening_balance',
                'purchase',
                'issue',
                'adjustment',
                'transfer_out',
                'transfer_in',
            ]);

            // Primary store this transaction affects (where stock is added/removed).
            $table->foreignId('store_id')->constrained('stores');

            // Only relevant for type = purchase.
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');

            // Only relevant for type = transfer_out / transfer_in — links the pair together.
            $table->foreignId('linked_transaction_id')->nullable()
                ->constrained('stock_transactions')->nullOnDelete();

            $table->date('transaction_date');
            $table->text('remarks')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
