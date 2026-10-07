<?php

namespace App\Http\Requests\DeviceToken;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Checks the data sent when a browser or phone registers (or removes) its push token.
 */
class DeviceTokenRequest extends FormRequest
{
    /**
     * Any logged-in user may manage their own device tokens.
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
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'in:web,android,ios'],
            'device_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * The messages shown when a rule fails.
     */
    public function messages(): array
    {
        return [
            'token.required' => __('Device Token is Required'),
            'token.max' => __('Device Token may not exceed 512 Characters'),
            'platform.in' => __('Platform must be web, android or ios'),
            'device_name.max' => __('Device Name may not exceed 150 Characters'),
        ];
    }
}
