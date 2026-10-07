<?php

namespace App\Listeners\Notifications;

use App\Events\Contracts\NotifiableEvent;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single listener for every business event that has the NotifiableEvent label.
 * Laravel finds it automatically (it has a handle() method), so it is not registered anywhere.
 *
 * It only passes the event to NotificationService. It never sends anything itself.
 *
 * - ShouldHandleEventsAfterCommit: it runs only AFTER the business database
 *   transaction is saved. If the business action is rolled back, no notification is made.
 * - Everything is caught: a notification problem must never break the business action.
 */
class ProcessNotificationEvent implements ShouldHandleEventsAfterCommit
{
    /**
     * Keeps the service that creates notifications.
     */
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * Creates the notification for the event. Any error is only written to the log.
     */
    public function handle(NotifiableEvent $event): void
    {
        try {
            $this->notificationService->createAutomaticNotification(
                $event->notificationEventCode(),
                $event->notificationData(),
                $event->notificationActor(),
            );
        } catch (Throwable $exception) {
            Log::error('Notification could not be created for event '.$event->notificationEventCode().': '.$exception->getMessage());
        }
    }
}
