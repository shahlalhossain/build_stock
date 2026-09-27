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
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // System Generated, e.g. PUR-0001

            // Optional link back to the Requisition this Purchase Fulfills.
            $table->foreignId('requisition_id')->nullable()
                ->constrained('product_requisitions')->nullOnDelete();

            $table->foreignId('store_id')->constrained('stores'); // Receiving Store
            $table->foreignId('supplier_id')->constrained('suppliers');

            $table->date('transaction_date');
            $table->text('remarks')->nullable();

            // Purchase / Invoice Details.
            $table->string('invoice_number')->nullable();
            $table->date('supplier_invoice_date')->nullable();
            $table->string('invoice_attachment_path')->nullable();

            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable()->default(0);
            $table->decimal('tax_amount', 15, 2)->nullable()->default(0);
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable();

            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->nullable()->default('unpaid');
            $table->decimal('paid_amount', 15, 2)->nullable()->default(0);

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
    }
};
