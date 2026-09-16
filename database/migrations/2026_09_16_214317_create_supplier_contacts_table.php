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
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();

            $table->integer('supplier_id');

            $table->string('name', 150);
            $table->string('designation', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('mobile', 30)->nullable();

            $table->boolean('is_primary')->default(false);

            $table->string('contact_type', 50)->nullable();

            $table->text('remarks')->nullable();

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('supplier_contacts');
    }
};
