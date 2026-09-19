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

            $table->enum('type', ['store', 'warehouse'])->default('store');

            $table->string('name', 255);
            $table->string('code', 50)->unique(); // System Generated Value

            $table->string('description', 255)->nullable();

            $table->string('mobile', 30)->nullable(); // Store Contact Mobile
            $table->string('email', 150)->nullable(); // Store Contact Email

            $table->unsignedBigInteger('manager_id')->nullable();  // Store Manager
            $table->unsignedBigInteger('storekeeper_id')->nullable(); // Storekeeper

            $table->boolean('is_active')->default(true);

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

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
