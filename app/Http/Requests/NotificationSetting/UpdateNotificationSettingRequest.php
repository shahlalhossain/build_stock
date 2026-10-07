<?php

namespace App\Http\Requests\NotificationSetting;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Class UpdateNotificationSettingRequest.
 */
class UpdateNotificationSettingRequest extends StoreNotificationSettingRequest
{
    /**
     * The same checks as create, but the setting being edited may keep its own event code.
     */
    protected function eventCodeRule(): Unique
    {
        $setting = $this->route('notificationSetting');

        return Rule::unique('notification_settings', 'event_code')
            ->whereNull('deleted_at')
            ->ignore($setting);
    }
}
