<?php

namespace App\Http\Requests\ProductUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreProductUnitRequest.
 */
class StoreProductUnitRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('product_units')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_units')],
            'description' => ['nullable', 'string', 'max:255'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Product-Unit Name is Required'),
            'name.string' => __('Product-Unit Name must be a Valid String'),
            'name.max' => __('Product-Unit Name may not exceed 255 Characters'),
            'name.unique' => __('This Product-Unit already Exists'),

            'slug.required' => __('Product-Unit Slug is Required'),
            'slug.string' => __('Product-Unit Slug must be a Valid String'),
            'slug.max' => __('Product-Unit Slug may not exceed 255 Characters'),
            'slug.unique' => __('This Product-Unit Slug already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'priority_order.numeric' => __('Priority Order must be a Valid Number'),
            'priority_order.min' => __('Priority Order must be 0 or Greater'),
        ];
    }
}
