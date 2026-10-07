<?php

namespace App\Models;

use App\Enums\NotificationReceiverStatus;
use Database\Factories\NotificationReceiverFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One delivery: one user, on one channel, for one notification.
 * This row tells us if the delivery was sent, delivered or failed.
 */
class NotificationReceiver extends Model
{
    /** @use HasFactory<NotificationReceiverFactory> */
    use HasFactory;

    protected $fillable = [
        'notification_message_id',
        'user_id',
        'channel_id',
        'recipient',
        'status',
        'attempts',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'user_deleted_at',
        'failed_at',
        'next_retry_at',
        'provider_message_id',
        'provider_status',
        'provider_response',
        'error_message',
    ];

    /**
     * Tells Laravel how to read these columns (enum, dates, list).
     */
    protected function casts(): array
    {
        return [
            'status' => NotificationReceiverStatus::class,
            'attempts' => 'integer',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'user_deleted_at' => 'datetime',
            'failed_at' => 'datetime',
            'next_retry_at' => 'datetime',
            'provider_response' => 'array',
        ];
    }

    /**
     * Only deliveries with the given status.
     */
    public function scopeWithStatus(Builder $query, NotificationReceiverStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * The notification this delivery belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(NotificationMessage::class, 'notification_message_id');
    }

    /**
     * The user who should receive this delivery.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The channel used for this delivery.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }

    /**
     * The step-by-step history of this delivery.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'notification_receiver_id');
    }

    /**
     * Adds one line to this delivery's history (used for troubleshooting).
     *
     * @param  array<string, mixed>|null  $request  What we asked the provider (no secrets).
     * @param  array<string, mixed>|null  $response  What the provider answered (no secrets).
     */
    public function addLog(string $event, ?string $status = null, ?string $message = null, ?array $request = null, ?array $response = null): NotificationLog
    {
        return $this->logs()->create([
            'event' => $event,
            'status' => $status,
            'message' => $message,
            'request_payload' => $request,
            'response_payload' => $response,
        ]);
    }
}
