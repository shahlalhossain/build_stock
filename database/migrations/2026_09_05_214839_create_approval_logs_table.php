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
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->enum('action_name', ['approved', 'rejected']);

            $table->foreignId('actioned_by')->nullable();
            $table->timestamp('actioned_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
