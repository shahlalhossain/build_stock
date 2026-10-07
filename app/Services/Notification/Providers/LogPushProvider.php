<?php

namespace App\Services\Notification\Providers;

use App\Models\UserDeviceToken;
use App\Services\Notification\NotificationResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A "pretend" push provider: it only writes the message to the application log.
 * Used while developing and in tests, so nothing is sent to the outside world.
 */
class LogPushProvider implements PushProvider
{
    /**
     * Writes the push message to the log and reports success.
     * Only the hidden form of the token is logged.
     *
     * @param  array<string, mixed>  $data
     */
    public function send(UserDeviceToken $device, string $title, string $body, array $data = []): NotificationResult
    {
        Log::info('[push:log] Push message', [
            'device' => $device->maskedToken(),
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        return NotificationResult::success('log-'.Str::uuid(), 'sent', ['provider' => 'log']);
    }
}
