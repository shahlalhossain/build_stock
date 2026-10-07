<?php

namespace App\Enums;

/**
 * The progress of one delivery (one user on one channel).
 */
enum NotificationReceiverStatus: string
{
    case Pending = 'pending';         // Created, not yet given to the queue
    case Queued = 'queued';           // Waiting in the queue
    case Processing = 'processing';   // The worker is sending it now
    case Sent = 'sent';               // Handed to the provider (does NOT mean the user saw it)
    case Delivered = 'delivered';     // The provider confirmed it reached the user
    case Failed = 'failed';           // Could not be sent
    case Cancelled = 'cancelled';     // Stopped before sending

    /**
     * Returns a friendly name to show on screen.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Returns true when nothing more will happen to this delivery.
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::Sent, self::Delivered, self::Failed, self::Cancelled], true);
    }
}
