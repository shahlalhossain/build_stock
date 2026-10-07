<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in the history of a delivery (queued, sent, failed, retry...).
 * Used for troubleshooting. These rows are only added, never changed.
 */
class NotificationLog extends Model
{
    /** This table has a "created_at" column but no "updated_at" column. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'notification_receiver_id',
        'event',
        'status',
        'message',
        'request_payload',
        'response_payload',
    ];

    /**
     * Tells Laravel to read the payload columns as lists.
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The delivery this history line belongs to.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(NotificationReceiver::class, 'notification_receiver_id');
    }
}
