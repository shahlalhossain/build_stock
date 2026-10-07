<?php

namespace App\Enums;

/**
 * The overall progress of one notification (all of its receivers together).
 */
enum NotificationStatus: string
{
    case Draft = 'draft';             // Saved but not ready to send
    case Pending = 'pending';         // Created, waiting to be sent
    case Processing = 'processing';   // Being sent right now
    case Completed = 'completed';     // Every receiver got it
    case Partial = 'partial';         // Some receivers got it, some failed
    case Failed = 'failed';           // No receiver got it
    case Cancelled = 'cancelled';     // Stopped before sending

    /**
     * Returns a friendly name to show on screen.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Returns true when nothing more will happen to this notification.
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Partial, self::Failed, self::Cancelled], true);
    }
}
