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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();

            $table->text('description')->nullable();

            $table->text('site_address')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->decimal('estimated_budget', 15, 2)->nullable();
            $table->integer('project_manager_id')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->integer('priority_order')->default(0);

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
        Schema::dropIfExists('projects');
    }
};
