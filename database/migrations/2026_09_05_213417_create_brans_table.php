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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();

            $table->text('description')->nullable();

            $table->string('logo_image', 255)->nullable();
            $table->string('banner_image', 255)->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->integer('priority_order')->default(0);

            $table->boolean('is_active')->default(false);

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
        Schema::dropIfExists('brands');
    }
};
