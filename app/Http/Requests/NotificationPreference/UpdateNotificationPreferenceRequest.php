<?php

namespace App\Http\Requests\NotificationPreference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Checks the data sent when a user switches a notification channel on or off.
 */
class UpdateNotificationPreferenceRequest extends FormRequest
{
    /**
     * Any logged-in user may change their own switches.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rules the data must follow.
     */
    public function rules(): array
    {
        return [
            'channel_id' => ['required', 'integer', Rule::exists('notification_channels', 'id')->whereNull('deleted_at')],
            'is_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * The messages shown when a rule fails.
     */
    public function messages(): array
    {
        return [
            'channel_id.required' => __('Channel is Required'),
            'channel_id.exists' => __('This Channel does not Exist'),
            'is_enabled.required' => __('Please choose On or Off'),
            'is_enabled.boolean' => __('Please choose On or Off'),
        ];
    }
}
