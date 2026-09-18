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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            // Null = Head Office, otherwise the Project (Site) this Store belongs to.
            $table->integer('project_id')->nullable();

            $table->string('name', 255);
            $table->string('code', 50)->unique();

            $table->string('description', 255)->nullable();

            $table->enum('type', ['store', 'warehouse'])->default('store');

            // Store Contact Mobile
            $table->string('mobile', 30)->nullable();
            // Store Contact Email
            $table->string('email', 150)->nullable();

            // Store Manager
            $table->unsignedBigInteger('manager_id')->nullable();
            // Storekeeper
            $table->unsignedBigInteger('storekeeper_id')->nullable();

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
        Schema::dropIfExists('stores');
    }
};
