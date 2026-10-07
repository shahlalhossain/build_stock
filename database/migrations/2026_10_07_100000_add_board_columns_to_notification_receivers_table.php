<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds what the user does on their "My Notifications" board:
     * when they read a notification, and when they deleted (hid) it.
     */
    public function up(): void
    {
        Schema::table('notification_receivers', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('delivered_at');
            $table->timestamp('user_deleted_at')->nullable()->after('read_at');

            // Speeds up the board list and the unread count of one user.
            $table->index(['user_id', 'channel_id', 'status'], 'receiver_board_index');
        });
    }

    /**
     * Removes the columns again.
     */
    public function down(): void
    {
        Schema::table('notification_receivers', function (Blueprint $table) {
            $table->dropIndex('receiver_board_index');
            $table->dropColumn(['read_at', 'user_deleted_at']);
        });
    }
};
