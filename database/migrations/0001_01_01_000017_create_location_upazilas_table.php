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
        Schema::create('location_upazilas', function (Blueprint $table) {
            $table->id();

            $table->integer('division_id');
            $table->integer('district_id');

            $table->string('name_en', 100);
            $table->string('name_bn', 100)->nullable();

            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();

            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            $table->boolean('is_active')->default(1);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_upazilas');
    }
};
