<?php

namespace App\Enums;

/**
 * How a notification was started.
 */
enum NotificationType: string
{
    case Automatic = 'automatic'; // Started by the system when a business event happens
    case Manual = 'manual';       // Started by a person from the Notification screen

    /**
     * Returns a friendly name to show on screen.
     */
    public function label(): string
    {
        return match ($this) {
            self::Automatic => 'Automatic',
            self::Manual => 'Manual',
        };
    }
}
