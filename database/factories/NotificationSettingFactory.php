<?php

namespace Database\Factories;

use App\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationSetting>
 */
class NotificationSettingFactory extends Factory
{
    protected $model = NotificationSetting::class;

    /**
     * Fake data for a notification setting (used in tests).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $module = fake()->unique()->lexify('module????');

        return [
            'name' => ucfirst($module).' Created',
            'event_code' => $module.'.created',
            'is_active' => true,
        ];
    }
}
