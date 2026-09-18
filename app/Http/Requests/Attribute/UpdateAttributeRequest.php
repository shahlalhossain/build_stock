<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateAttributeRequest.
 */
class UpdateAttributeRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $attribute = $this->route('attribute');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($attribute)],
            'description' => ['nullable', 'string', 'max:255'],

            'values' => ['nullable', 'array'],
            'values.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Attribute Name is Required'),
            'name.string' => __('Attribute Name must be a Valid String'),
            'name.max' => __('Attribute Name may not exceed 255 Characters'),
            'name.unique' => __('This Attribute already Exists'),

            'description.string' => __('Attribute Description must be a Valid String'),
            'description.max' => __('Attribute Description may not exceed 255 Characters'),

            'values.array' => __('Attribute Values must be a Valid List'),
            'values.*.required' => __('Attribute Value cannot be Empty'),
            'values.*.string' => __('Attribute Value must be a Valid String'),
            'values.*.max' => __('Attribute Value may not exceed 255 Characters'),
            'values.*.distinct' => __('Duplicate Attribute Value is not Allowed'),
        ];
    }
}
