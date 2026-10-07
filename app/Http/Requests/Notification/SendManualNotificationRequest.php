<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Checks the data of a manual notification (used by both "Preview" and "Send").
 * Who may send is decided by the route's "notification.send" permission.
 */
class SendManualNotificationRequest extends FormRequest
{
    /**
     * Access is controlled by the route permission, so this is always true.
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
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(NotificationPriority::class)],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['integer', Rule::exists('notification_channels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'users' => ['nullable', 'array'],
            'users.*' => ['integer', Rule::exists('users', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->whereNull('deleted_at')],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Extra check: at least one person or one role must be chosen.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (empty($this->input('users')) && empty($this->input('roles'))) {
                $validator->errors()->add('users', __('Choose at least one user or one role to receive the notification.'));
            }
        });
    }

    /**
     * The messages shown when a rule fails.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('Title is Required'),
            'title.max' => __('Title may not exceed 255 Characters'),
            'message.required' => __('Message is Required'),
            'message.max' => __('Message may not exceed 2000 Characters'),
            'priority.required' => __('Priority is Required'),
            'priority.enum' => __('Priority must be low, normal, high or urgent'),
            'channels.required' => __('Choose at least one Channel'),
            'channels.min' => __('Choose at least one Channel'),
            'channels.*.exists' => __('One of the chosen Channels is not available'),
            'users.*.exists' => __('One of the chosen Users is not available'),
            'roles.*.exists' => __('One of the chosen Roles does not Exist'),
            'scheduled_at.date' => __('Scheduled Time must be a valid Date and Time'),
            'scheduled_at.after' => __('Scheduled Time must be in the Future'),
        ];
    }
}
