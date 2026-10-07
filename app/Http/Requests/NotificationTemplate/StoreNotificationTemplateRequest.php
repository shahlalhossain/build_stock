<?php

namespace App\Http\Requests\NotificationTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreNotificationTemplateRequest.
 */
class StoreNotificationTemplateRequest extends FormRequest
{
    use ChecksTemplatePlaceholders;

    /**
     * Anyone who reaches this request may use it (route middleware checks the permission).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rules the submitted form data must follow.
     */
    public function rules(): array
    {
        return [
            'notification_setting_id' => ['required', 'integer', Rule::exists('notification_settings', 'id')->whereNull('deleted_at')],
            'channel_id' => [
                'required',
                'integer',
                Rule::exists('notification_channels', 'id')->whereNull('deleted_at'),
                Rule::unique('notification_templates', 'channel_id')->where('notification_setting_id', $this->input('notification_setting_id')),
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'variables' => ['nullable', 'string'],
        ];
    }

    /**
     * Adds the placeholder check after the normal rules.
     */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->checkPlaceholders($validator)];
    }

    /**
     * The friendly error messages shown to the user.
     */
    public function messages(): array
    {
        return [
            'notification_setting_id.required' => __('Notification Setting is Required'),
            'notification_setting_id.exists' => __('Selected Notification Setting is not Valid'),

            'channel_id.required' => __('Channel is Required'),
            'channel_id.exists' => __('Selected Channel is not Valid'),
            'channel_id.unique' => __('A Template for this Setting and Channel already Exists'),

            'subject.string' => __('Subject must be a Valid String'),
            'subject.max' => __('Subject may not exceed 255 Characters'),

            'title.string' => __('Title must be a Valid String'),
            'title.max' => __('Title may not exceed 255 Characters'),

            'body.required' => __('Body is Required'),
            'body.string' => __('Body must be a Valid String'),
            'body.max' => __('Body may not exceed 5000 Characters'),

            'variables.string' => __('Variables must be a Valid String'),
        ];
    }
}
