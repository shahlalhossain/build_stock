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
        Schema::create('addresses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('model_name');
            $table->integer('model_id')->unsigned();

            $table->string('address_type')->default('Present')->comment('Present, Permanent');

            $table->string('address');
            $table->string('address_bn')->nullable();

            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('map_address')->nullable();

            $table->integer('division_id')->unsigned()->nullable();
            $table->string('division_name')->nullable();
            $table->integer('district_id')->unsigned()->nullable();
            $table->string('district_name')->nullable();
            $table->integer('thana_id')->unsigned()->nullable();
            $table->string('thana_name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
