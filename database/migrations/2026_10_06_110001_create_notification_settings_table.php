<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that says which business events (like "brand.created") send notifications.
     */
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150); // Display name, e.g. "Brand Created"
            $table->string('event_code', 100)->index(); // e.g. "brand.created"

            // Only for information. The notification system never checks these itself.
            $table->string('permission_name', 150)->nullable();
            $table->string('permission_code', 50)->nullable();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
