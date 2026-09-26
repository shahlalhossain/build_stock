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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('remarks');
            $table->date('supplier_invoice_date')->nullable()->after('invoice_number');
            $table->string('invoice_attachment_path')->nullable()->after('supplier_invoice_date');

            $table->enum('discount_type', ['fixed', 'percentage'])->nullable()->after('invoice_attachment_path');
            $table->decimal('discount_amount', 15, 2)->nullable()->default(0)->after('discount_type');
            $table->decimal('tax_amount', 15, 2)->nullable()->default(0)->after('discount_amount');
            $table->decimal('total_amount', 15, 2)->nullable()->after('tax_amount');
            $table->decimal('net_amount', 15, 2)->nullable()->after('total_amount');

            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->nullable()->default('unpaid')->after('net_amount');
            $table->decimal('paid_amount', 15, 2)->nullable()->default(0)->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'supplier_invoice_date',
                'invoice_attachment_path',
                'discount_type',
                'discount_amount',
                'tax_amount',
                'total_amount',
                'net_amount',
                'payment_status',
                'paid_amount',
            ]);
        });
    }
};
