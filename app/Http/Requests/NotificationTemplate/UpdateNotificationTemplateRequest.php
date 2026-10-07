<?php

namespace App\Http\Requests\NotificationTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class UpdateNotificationTemplateRequest.
 *
 * The setting and channel are fixed after creation, so they are not accepted here.
 */
class UpdateNotificationTemplateRequest extends FormRequest
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
            'subject' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'variables' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
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
            'subject.string' => __('Subject must be a Valid String'),
            'subject.max' => __('Subject may not exceed 255 Characters'),

            'title.string' => __('Title must be a Valid String'),
            'title.max' => __('Title may not exceed 255 Characters'),

            'body.required' => __('Body is Required'),
            'body.string' => __('Body must be a Valid String'),
            'body.max' => __('Body may not exceed 5000 Characters'),

            'variables.string' => __('Variables must be a Valid String'),

            'is_active.boolean' => __('Active must be Yes or No'),
        ];
    }
}
