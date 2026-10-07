<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's own on/off switch for one notification channel.
 */
class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'channel_id',
        'is_enabled',
    ];

    /**
     * Tells Laravel to read "is_enabled" as a real true/false value.
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Only the switches that are turned on.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * The user who owns this switch.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The channel this switch controls.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }
}
