<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the table that remembers each user's browser/phone address
     * (called a "device token"), which push notifications are sent to.
     */
    public function up(): void
    {
        Schema::create('user_device_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('token', 512)->unique();
            $table->string('platform', 20)->default('web'); // web, android or ios
            $table->string('device_name', 150)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Removes the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_device_tokens');
    }
};
