<?php

namespace Database\Factories;

use App\Enums\NotificationReceiverStatus;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationReceiver>
 */
class NotificationReceiverFactory extends Factory
{
    protected $model = NotificationReceiver::class;

    /**
     * Fake data for one delivery (used in tests).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_message_id' => NotificationMessage::factory(),
            'user_id' => User::factory(),
            'channel_id' => NotificationChannel::factory(),
            'status' => NotificationReceiverStatus::Pending,
            'attempts' => 0,
        ];
    }
}
