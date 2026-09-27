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
        Schema::create('product_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // System Generated, e.g. TRF-0001

            // Optional link back to the Requisition this Transfer Fulfills.
            $table->foreignId('requisition_id')->nullable()
                ->constrained('product_requisitions')->nullOnDelete();

            // Both Stores are real, persisted columns — unlike the old
            // transfer_out/transfer_in linked-pair design, a Transfer is ONE row.
            $table->foreignId('source_store_id')->constrained('stores');
            $table->foreignId('destination_store_id')->constrained('stores');

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transfers');
    }
};
