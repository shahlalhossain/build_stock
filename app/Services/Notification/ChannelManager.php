<?php

namespace App\Services\Notification;

use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use App\Models\NotificationReceiver;
use App\Services\Notification\Channels\NotificationChannelInterface;
use Closure;

/**
 * Finds the right channel class (push, sms, email...) for a channel
 * and asks it to send. There are no big if/else lists: the list of
 * channel classes lives in config/notification.php.
 */
class ChannelManager
{
    /**
     * Channel classes added in code (mainly for tests), keyed by driver name.
     *
     * @var array<string, Closure|string>
     */
    protected array $extraDrivers = [];

    /**
     * Adds (or replaces) a channel class for a driver name.
     * Accepts a class name or a function that returns the channel object.
     */
    public function extend(string $driverName, Closure|string $channel): void
    {
        $this->extraDrivers[$driverName] = $channel;
    }

    /**
     * Returns the channel object for a driver name, such as "push".
     *
     * @throws GeneralException when no class is registered for the driver.
     */
    public function driver(string $driverName): NotificationChannelInterface
    {
        $registered = $this->extraDrivers[$driverName] ?? config("notification.drivers.{$driverName}");

        if ($registered === null) {
            throw new GeneralException("No channel class is registered for driver '{$driverName}'.");
        }

        $channel = $registered instanceof Closure ? $registered() : app($registered);

        if (! $channel instanceof NotificationChannelInterface) {
            throw new GeneralException("The class for driver '{$driverName}' must implement NotificationChannelInterface.");
        }

        return $channel;
    }

    /**
     * Returns the channel object for a channel record.
     */
    public function forChannel(NotificationChannel $channel): NotificationChannelInterface
    {
        return $this->driver($channel->driver);
    }

    /**
     * Sends one delivery through the channel it belongs to.
     */
    public function send(NotificationReceiver $receiver): NotificationResult
    {
        return $this->forChannel($receiver->channel)->send($receiver);
    }
}
