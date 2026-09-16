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
        Schema::create('supplier_payment_accounts', function (Blueprint $table) {
            $table->id();

            $table->integer('supplier_id');

            $table->string('payment_method', 50);

            $table->string('account_name', 150);
            $table->string('account_number', 100);
            $table->string('bank_name', 150);
            $table->string('branch_name', 150);
            $table->string('routing_number', 50)->nullable();

            $table->boolean('is_primary')->default(false);

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('supplier_payment_accounts');
    }
};
