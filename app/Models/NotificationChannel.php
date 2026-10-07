<?php

namespace App\Models;

use Database\Factories\NotificationChannelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A way of sending notifications, such as push, sms or email.
 */
class NotificationChannel extends Model
{
    /** @use HasFactory<NotificationChannelFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'driver',
        'description',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Tells Laravel to treat these columns as real booleans/numbers.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Tells the activity log to record every change made to this record.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('notification_channel')
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Only the channels that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Shows channels in the order chosen by "sort_order".
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The notification settings that use this channel.
     */
    public function settings(): BelongsToMany
    {
        return $this->belongsToMany(NotificationSetting::class, 'notification_setting_channels', 'channel_id', 'notification_setting_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * The message templates written for this channel.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'channel_id');
    }

    /**
     * Every delivery that went through this channel.
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(NotificationReceiver::class, 'channel_id');
    }

    /**
     * The user who created this channel.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this channel.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The user who deleted this channel.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
