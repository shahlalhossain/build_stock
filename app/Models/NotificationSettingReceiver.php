<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One "who gets it" rule of a notification setting.
 * receiver_type is "user", "role" or "permission".
 * receiver_value is the user id, the role name or the permission name.
 */
class NotificationSettingReceiver extends Model
{
    public const TYPE_USER = 'user';

    public const TYPE_ROLE = 'role';

    public const TYPE_PERMISSION = 'permission';

    protected $fillable = [
        'notification_setting_id',
        'receiver_type',
        'receiver_value',
    ];

    /**
     * The setting this rule belongs to.
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class, 'notification_setting_id');
    }
}
