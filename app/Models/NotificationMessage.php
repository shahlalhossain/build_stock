<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Database\Factories\NotificationMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One notification created by the system or by a person.
 * It is sent to many receivers (see NotificationReceiver).
 *
 * Named "NotificationMessage" so it does not clash with Laravel's own
 * "notifications" table and classes.
 */
class NotificationMessage extends Model
{
    /** @use HasFactory<NotificationMessageFactory> */
    use HasFactory;

    protected $table = 'notification_messages';

    protected $fillable = [
        'notification_setting_id',
        'type',
        'event_code',
        'title',
        'message_body',
        'data',
        'priority',
        'status',
        'is_active',
        'scheduled_at',
        'created_by',
    ];

    /**
     * Tells Laravel how to read these columns (enums, list, date, true/false).
     */
    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'priority' => NotificationPriority::class,
            'status' => NotificationStatus::class,
            'data' => 'array',
            'is_active' => 'boolean',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Only notifications with the given status.
     */
    public function scopeWithStatus(Builder $query, NotificationStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * Only notifications of the given type (automatic or manual).
     */
    public function scopeOfType(Builder $query, NotificationType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /**
     * The setting this notification was created from (empty for manual ones).
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class, 'notification_setting_id');
    }

    /**
     * Every delivery (user + channel) of this notification.
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(NotificationReceiver::class, 'notification_message_id');
    }

    /**
     * The user who started this notification (empty if the system did).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
