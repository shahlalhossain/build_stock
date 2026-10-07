<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table with one row for each "user + channel" delivery of a notification.
     * This is where we track if each delivery was sent, delivered or failed.
     */
    public function up(): void
    {
        Schema::create('notification_receivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_message_id')->constrained('notification_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();

            $table->string('recipient', 255)->nullable(); // Where we send it, e.g. device token, phone or email
            $table->string('status', 20)->default('pending')->index(); // pending, queued, processing, sent, delivered, failed, cancelled
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();

            $table->string('provider_message_id', 255)->nullable()->index();
            $table->string('provider_status', 100)->nullable();
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // The same user must not get the same notification twice on the same channel.
            $table->unique(['notification_message_id', 'user_id', 'channel_id'], 'receiver_unique');
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_receivers');
    }
};
