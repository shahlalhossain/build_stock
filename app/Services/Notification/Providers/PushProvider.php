<?php

namespace App\Services\Notification\Providers;

use App\Models\UserDeviceToken;
use App\Services\Notification\NotificationResult;

/**
 * The rule every push provider (log, Firebase...) must follow.
 * A provider only knows how to talk to ONE outside service.
 * It must never put secrets (keys, full tokens) into the result.
 */
interface PushProvider
{
    /**
     * Sends one push message to one device and returns the outcome.
     * Return NotificationResult::failure(..., providerStatus: NotificationResult::STATUS_INVALID_TOKEN)
     * when the device address is no longer valid.
     *
     * @param  array<string, mixed>  $data  Extra values for the app (such as url).
     */
    public function send(UserDeviceToken $device, string $title, string $body, array $data = []): NotificationResult;
}
