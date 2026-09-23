<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreProductRequest.
 */
class StoreProductRequest extends FormRequest
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
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'unit_id' => ['nullable', 'integer', Rule::exists('product_units', 'id')],

            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')],
            'description' => ['nullable', 'string', 'max:255'],

            'attribute_value_ids' => ['nullable', 'array'],
            'attribute_value_ids.*' => ['integer', Rule::exists('attribute_values', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('Category is Required'),
            'category_id.exists' => __('Selected Category does not Exist'),

            'sub_category_id.exists' => __('Selected Sub-Category does not Exist'),
            'brand_id.exists' => __('Selected Brand does not Exist'),
            'unit_id.exists' => __('Selected Unit does not Exist'),

            'name.required' => __('Product Name is Required'),
            'name.string' => __('Product Name must be a Valid String'),
            'name.max' => __('Product Name may not exceed 255 Characters'),

            'sku.string' => __('SKU must be a Valid String'),
            'sku.max' => __('SKU may not exceed 255 Characters'),
            'sku.unique' => __('This SKU already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'attribute_value_ids.*.exists' => __('One of the Selected Attribute Values does not Exist'),
        ];
    }
}
