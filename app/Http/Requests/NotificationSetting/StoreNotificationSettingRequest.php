<?php

namespace App\Http\Requests\NotificationSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Class StoreNotificationSettingRequest.
 */
class StoreNotificationSettingRequest extends FormRequest
{
    /**
     * Everyone who reaches this route is allowed (route middleware checks the permission).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The checks every new setting must pass.
     */
    public function rules(): array
    {
        return $this->baseRules($this->eventCodeRule());
    }

    /**
     * The event code must be unique among settings that are not in the trash.
     */
    protected function eventCodeRule(): Unique
    {
        return Rule::unique('notification_settings', 'event_code')->whereNull('deleted_at');
    }

    /**
     * The checks shared by the create and update forms.
     */
    protected function baseRules(Unique $eventCodeUniqueRule): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'event_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/', $eventCodeUniqueRule],
            'permission_name' => ['nullable', 'string', 'max:150'],
            'permission_code' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['integer', Rule::exists('notification_channels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'receiver_users' => ['nullable', 'array'],
            'receiver_users.*' => ['integer', 'exists:users,id'],
            'receiver_roles' => ['nullable', 'array'],
            'receiver_roles.*' => ['string', 'exists:roles,name'],
            'receiver_permissions' => ['nullable', 'array'],
            'receiver_permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    /**
     * The friendly messages shown when a check fails.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Setting Name is Required'),
            'name.string' => __('Setting Name must be a Valid String'),
            'name.max' => __('Setting Name may not exceed 150 Characters'),

            'event_code.required' => __('Event Code is Required'),
            'event_code.string' => __('Event Code must be a Valid String'),
            'event_code.max' => __('Event Code may not exceed 100 Characters'),
            'event_code.regex' => __('Event Code must look like brand.created (lowercase letters, numbers, underscores and dots)'),
            'event_code.unique' => __('This Event Code already Exists'),

            'permission_name.max' => __('Permission Name may not exceed 150 Characters'),
            'permission_code.max' => __('Permission Code may not exceed 150 Characters'),
            'description.max' => __('Description may not exceed 500 Characters'),

            'channels.array' => __('Channels must be a Valid List'),
            'channels.*.exists' => __('One of the chosen Channels is not Available'),
            'receiver_users.*.exists' => __('One of the chosen Users does not Exist'),
            'receiver_roles.*.exists' => __('One of the chosen Roles does not Exist'),
            'receiver_permissions.*.exists' => __('One of the chosen Permissions does not Exist'),
        ];
    }
}
