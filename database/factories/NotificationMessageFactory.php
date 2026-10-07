<?php

namespace Database\Factories;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationMessage>
 */
class NotificationMessageFactory extends Factory
{
    protected $model = NotificationMessage::class;

    /**
     * Fake data for a notification (used in tests).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => NotificationType::Automatic,
            'title' => fake()->sentence(3),
            'message_body' => fake()->sentence(),
            'priority' => NotificationPriority::Normal,
            'status' => NotificationStatus::Pending,
            'is_active' => true,
        ];
    }
}
