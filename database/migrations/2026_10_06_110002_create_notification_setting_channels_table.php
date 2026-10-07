<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the link table: which channels each notification setting uses.
     */
    public function up(): void
    {
        Schema::create('notification_setting_channels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_setting_id')->constrained('notification_settings')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // The same channel cannot be added twice to the same setting.
            $table->unique(['notification_setting_id', 'channel_id'], 'setting_channel_unique');
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_setting_channels');
    }
};
