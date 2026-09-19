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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->integer('supplier_type_id');

            $table->string('code', 30)->unique(); // System Generated Value

            $table->string('name', 255);

            $table->text('description')->nullable();

            $table->string('tin_number', 50)->nullable();
            $table->string('bin_number', 50)->nullable();

            $table->decimal('credit_limit', 18, 2)->default(0);
            $table->decimal('minimum_order_quantity', 18, 3)->default(0);
            $table->decimal('minimum_order_amount', 18, 2)->default(0);
            $table->unsignedInteger('payment_terms_days')->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);

            $table->integer('ledger_account_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->enum('status', ['pending', 'approved', 'rejected']) ->default('pending');

            $table->text('remarks')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
