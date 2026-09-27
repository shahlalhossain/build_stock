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
        Schema::create('product_receives', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // System Generated, e.g. RCV-0001

            // A Receive only ever exists to confirm arrival of an Approved Transfer.
            // A single Transfer may have MULTIPLE Receives (partial receipt over time).
            $table->foreignId('transfer_id')->constrained('product_transfers');

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
        Schema::dropIfExists('product_receives');
    }
};
