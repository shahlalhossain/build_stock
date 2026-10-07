<?php

namespace App\Events\Contracts;

use App\Models\User;

/**
 * Put this on any business event that should be able to send notifications.
 * The notification system listens for every event with this label,
 * so nothing else needs to change when a new module adds its own events.
 */
interface NotifiableEvent
{
    /**
     * The event code, such as "brand.created". It must match the
     * "event code" of a Notification Setting.
     */
    public function notificationEventCode(): string;

    /**
     * The values that message templates can use, for example brand_name.
     * Keep it to plain text and numbers, never secrets.
     *
     * @return array<string, mixed>
     */
    public function notificationData(): array;

    /**
     * The person who did the action (or null if the system did it).
     */
    public function notificationActor(): ?User;
}
