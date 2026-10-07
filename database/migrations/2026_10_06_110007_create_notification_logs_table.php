<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that keeps a step-by-step history of every delivery (for troubleshooting).
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_receiver_id')->constrained('notification_receivers')->cascadeOnDelete();

            $table->string('event', 50); // queued, processing, sent, delivered, failed, retry...
            $table->string('status', 20)->nullable();
            $table->text('message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
