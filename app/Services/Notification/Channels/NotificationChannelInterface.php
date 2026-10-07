<?php

namespace App\Services\Notification\Channels;

use App\Models\NotificationReceiver;
use App\Services\Notification\NotificationResult;

/**
 * The rule every channel (push, sms, email...) must follow:
 * it can send one delivery and tell us what happened.
 */
interface NotificationChannelInterface
{
    /**
     * Sends the message to one receiver and returns the outcome.
     * A channel must NOT throw for normal failures; it returns NotificationResult::failure().
     */
    public function send(NotificationReceiver $receiver): NotificationResult;
}
