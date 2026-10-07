<?php

namespace Database\Seeders;

use App\Models\NotificationChannel;
use Illuminate\Database\Seeder;

/**
 * Adds the notification channels we support.
 * "push" (pop-up to a phone/browser) and "realtime" (live message + the user's
 * "My Notifications" board). Safe to run many times.
 * Run with: php artisan db:seed --class=NotificationChannelSeeder
 */
class NotificationChannelSeeder extends Seeder
{
    /**
     * Saves the "push" and "realtime" channels (if they are not already there).
     */
    public function run(): void
    {
        NotificationChannel::firstOrCreate(
            ['code' => 'push'],
            [
                'name' => 'Push Notification',
                'driver' => 'push',
                'description' => 'Pop-up message sent to the user\'s browser or phone.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        NotificationChannel::firstOrCreate(
            ['code' => 'realtime'],
            [
                'name' => 'Real-time Notification',
                'driver' => 'realtime',
                'description' => 'Live message in the open browser and a saved copy on the "My Notifications" board.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }
}
