<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that holds each notification the system creates.
     * (Named "notification_messages" so it does not clash with Laravel's own "notifications" table.)
     */
    public function up(): void
    {
        Schema::create('notification_messages', function (Blueprint $table) {
            $table->id();

            // If the setting is deleted, we keep the notification history.
            $table->foreignId('notification_setting_id')->nullable()->constrained('notification_settings')->nullOnDelete();

            $table->string('type', 20)->index(); // "automatic" or "manual"
            $table->string('event_code', 100)->nullable()->index();

            $table->string('title', 255)->nullable();
            $table->text('message_body');
            $table->json('data')->nullable(); // Extra details such as model name, model id, url

            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->string('status', 20)->default('pending')->index(); // pending, processing, completed, partial, failed, cancelled
            $table->boolean('is_active')->default(true);

            $table->timestamp('scheduled_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_messages');
    }
};
