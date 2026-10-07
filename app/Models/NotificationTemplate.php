<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * The message text for one setting on one channel.
 * The text may hold placeholders such as {{brand_name}}.
 */
class NotificationTemplate extends Model
{
    use LogsActivity;

    protected $fillable = [
        'notification_setting_id',
        'channel_id',
        'subject',
        'title',
        'body',
        'variables',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * Tells Laravel to read "variables" as a list and "is_active" as true/false.
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tells the activity log to record every change made to this record.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('notification_template')
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Only the templates that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The setting this template belongs to.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class, 'notification_setting_id');
    }

    /**
     * The channel this template is written for.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }

    /**
     * The user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this template.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
