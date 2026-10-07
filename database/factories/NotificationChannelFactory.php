<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationChannel>
 */
class NotificationChannelFactory extends Factory
{
    protected $model = NotificationChannel::class;

    /**
     * Fake data for a notification channel (used in tests).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->lexify('chan_????');

        return [
            'name' => ucfirst($code),
            'code' => $code,
            'driver' => $code,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
