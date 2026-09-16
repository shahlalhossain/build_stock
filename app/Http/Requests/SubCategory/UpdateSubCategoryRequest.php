<?php

namespace App\Http\Requests\SubCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateSubCategoryRequest.
 */
class UpdateSubCategoryRequest extends FormRequest
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
        $subCategory = $this->route('sub_category');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255', Rule::unique('sub_categories', 'name')->ignore($subCategory)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('sub_categories', 'slug')->ignore($subCategory)],
            'description' => ['nullable', 'string', 'max:255'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('Category is Required'),
            'category_id.integer' => __('Category must be Valid'),
            'category_id.exists' => __('Selected Category does not Exist'),

            'name.required' => __('Sub-Category Name is Required'),
            'name.string' => __('Sub-Category Name must be a Valid String'),
            'name.max' => __('Sub-Category Name may not exceed 255 Characters'),
            'name.unique' => __('This Sub-Category already Exists'),

            'slug.required' => __('Sub-Category Slug is Required'),
            'slug.string' => __('Sub-Category Slug must be a Valid String'),
            'slug.max' => __('Sub-Category Slug may not exceed 255 Characters'),
            'slug.unique' => __('This Sub-Category Slug already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'priority_order.numeric' => __('Priority Order must be a Valid Number'),
            'priority_order.min' => __('Priority Order must be 0 or Greater'),
        ];
    }
}
