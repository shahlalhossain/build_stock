<?php

namespace App\Models;

use Database\Factories\NotificationSettingFactory;
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
 * Says that a business event (for example "brand.created") should send notifications.
 * It also knows which channels to use and who should receive them.
 */
class NotificationSetting extends Model
{
    /** @use HasFactory<NotificationSettingFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'event_code',
        'permission_name',
        'permission_code',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Tells Laravel to treat "is_active" as a real true/false value.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tells the activity log to record every change made to this record.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('notification_setting')
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Only the settings that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Finds the setting for one event code, such as "brand.created".
     */
    public function scopeForEvent(Builder $query, string $eventCode): Builder
    {
        return $query->where('event_code', $eventCode);
    }

    /**
     * Every channel linked to this setting (switched on or off).
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(NotificationChannel::class, 'notification_setting_channels', 'notification_setting_id', 'channel_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Only the channels that can really be used: the channel itself is on
     * AND it is switched on for this setting.
     */
    public function activeChannels(): BelongsToMany
    {
        return $this->channels()
            ->wherePivot('is_active', true)
            ->where('notification_channels.is_active', true);
    }

    /**
     * The rules that say who receives this notification (users, roles or permissions).
     */
    public function receiverRules(): HasMany
    {
        return $this->hasMany(NotificationSettingReceiver::class, 'notification_setting_id');
    }

    /**
     * The message templates, one for each channel.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'notification_setting_id');
    }

    /**
     * The notifications that were created from this setting.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(NotificationMessage::class, 'notification_setting_id');
    }

    /**
     * The user who created this setting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this setting.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The user who deleted this setting.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
