<?php

namespace App\Services\Notification\Channels;

use App\Events\Notifications\NotificationReceived;
use App\Models\NotificationReceiver;
use App\Services\MyNotificationService;
use App\Services\Notification\NotificationResult;
use Throwable;

/**
 * The real-time channel. The notification itself is kept on the user's "My Notifications"
 * board (it is this very delivery row). Sending only means: "tell the user's open
 * browser right now" through Laravel Broadcasting (Pusher). A user who is offline simply
 * sees it on the board later.
 */
class RealtimeNotificationChannel implements NotificationChannelInterface
{
    /**
     * Keeps the board service that builds the data shown to the user.
     */
    public function __construct(protected MyNotificationService $board) {}

    /**
     * Broadcasts the notification to the user's private channel.
     * A broadcaster problem (for example Pusher is down) is a temporary failure and is retried.
     */
    public function send(NotificationReceiver $receiver): NotificationResult
    {
        $channelName = 'App.Models.User.'.$receiver->user_id;

        // This delivery is not "sent" yet, so it is not in the unread count: add it.
        $item = $this->board->toItem($receiver) + [
            'unread_count' => $this->board->unreadCount($receiver->user) + 1,
        ];

        try {
            event(new NotificationReceived($receiver->user_id, $item));
        } catch (Throwable $exception) {
            return NotificationResult::failure('Real-time broadcast failed: '.$exception->getMessage());
        }

        return NotificationResult::success(null, 'sent', ['channel' => $channelName, 'event' => 'notification.received']);
    }
}
