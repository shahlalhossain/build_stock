<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that stores the message text for each setting and channel.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_setting_id')->constrained('notification_settings')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();

            $table->string('subject', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('body'); // May contain placeholders like {{brand_name}}
            $table->json('variables')->nullable(); // Placeholders this template may use

            $table->boolean('is_active')->default(true);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Only one template per setting and channel.
            $table->unique(['notification_setting_id', 'channel_id'], 'template_setting_channel_unique');
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
