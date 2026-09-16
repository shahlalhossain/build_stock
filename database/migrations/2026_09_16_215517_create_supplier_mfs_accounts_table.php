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
        Schema::create('supplier_mfs_accounts', function (Blueprint $table) {
            $table->id();

            $table->integer('supplier_id');

            $table->string('mfs_operator_name', 50);
            $table->string('mfs_account_number', 30);

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
        Schema::dropIfExists('supplier_mfs_accounts');
    }
};
