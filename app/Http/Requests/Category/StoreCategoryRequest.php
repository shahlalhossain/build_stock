<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreCategoryRequest.
 */
class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories')],
            'description' => ['nullable', 'string', 'max:255'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Category Name is Required'),
            'name.string' => __('Category Name must be a Valid String'),
            'name.max' => __('Category Name may not exceed 255 Characters'),
            'name.unique' => __('This Category already Exists'),

            'slug.required' => __('Category Slug is Required'),
            'slug.string' => __('Category Slug must be a Valid String'),
            'slug.max' => __('Category Slug may not exceed 255 Characters'),
            'slug.unique' => __('This Category Slug already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'priority_order.numeric' => __('Priority Order must be a Valid Number'),
            'priority_order.min' => __('Priority Order must be 0 or Greater'),
        ];
    }
}
