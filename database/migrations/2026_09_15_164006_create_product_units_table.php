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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();

            $table->string('group', 255); // Quantity/Weight/Volume/Area/Length/Packaging/Structural/Logistics etc.

            $table->string('name', 255);
            $table->string('symbol', 100);

            $table->text('description')->nullable();
            $table->text('usage')->nullable();

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
        Schema::dropIfExists('product_units');
    }
};
