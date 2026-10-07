<?php

namespace App\Http\Requests\NotificationChannel;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Checks the form data when a notification channel is edited.
 * The code cannot be changed, so it is not accepted here.
 */
class UpdateNotificationChannelRequest extends FormRequest
{
    /**
     * Lets every logged-in user pass (permissions are checked on the route).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rules each field must follow.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'driver' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * The error messages shown when a rule fails.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Channel Name is Required'),
            'name.string' => __('Channel Name must be a Valid String'),
            'name.max' => __('Channel Name may not exceed 100 Characters'),

            'driver.required' => __('Driver is Required'),
            'driver.string' => __('Driver must be a Valid String'),
            'driver.max' => __('Driver may not exceed 100 Characters'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 500 Characters'),

            'sort_order.integer' => __('Sort Order must be a Valid Number'),
            'sort_order.min' => __('Sort Order must be 0 or Greater'),
        ];
    }
}
