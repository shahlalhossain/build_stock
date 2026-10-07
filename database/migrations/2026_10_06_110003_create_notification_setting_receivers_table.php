<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that says WHO gets the notification for each setting
     * (specific users, everyone with a role, or everyone with a permission).
     */
    public function up(): void
    {
        Schema::create('notification_setting_receivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_setting_id')->constrained('notification_settings')->cascadeOnDelete();

            $table->string('receiver_type', 20); // "user", "role" or "permission"
            $table->string('receiver_value', 150); // user id, role name or permission name

            $table->timestamps();

            $table->unique(['notification_setting_id', 'receiver_type', 'receiver_value'], 'setting_receiver_unique');
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_setting_receivers');
    }
};
