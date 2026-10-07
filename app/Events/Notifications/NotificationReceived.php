<?php

namespace App\Events\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * "A new notification arrived for this user." It is sent in real time to the
 * user's own private channel, so their open browser can show a pop-up and update the bell.
 *
 * It is sent immediately (not queued again) because it is already created inside a queue job.
 * The data must be plain text/numbers only: never secrets.
 */
class NotificationReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Remembers who gets it and what to show.
     *
     * @param  array<string, mixed>  $item  The board item (id, title, body, url, priority, is_read, time_human, unread_count...).
     */
    public function __construct(public int $userId, public array $item) {}

    /**
     * The private channel only this user may listen to.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.'.$this->userId);
    }

    /**
     * The event name the browser listens for.
     */
    public function broadcastAs(): string
    {
        return 'notification.received';
    }

    /**
     * The data sent to the browser.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->item;
    }
}
