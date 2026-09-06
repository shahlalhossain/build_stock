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
        Schema::create('mobile_verify_registers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('model_name');
            $table->integer('model_id');

            $table->string('mobile_number');

            $table->boolean('is_verified')->default(false);
            $table->string('status')->nullable();

            $table->integer('verify_by')->nullable();
            $table->timestamp('verify_at')->nullable();

            $table->integer('verification_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_verify_registers');
    }
};
