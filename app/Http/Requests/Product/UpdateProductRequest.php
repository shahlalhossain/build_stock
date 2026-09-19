<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest.
 */
class UpdateProductRequest extends FormRequest
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
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($product)],
            'description' => ['nullable', 'string', 'max:255'],
            'has_variants' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Product Name is Required'),
            'name.string' => __('Product Name must be a Valid String'),
            'name.max' => __('Product Name may not exceed 255 Characters'),

            'code.required' => __('Product Code is Required'),
            'code.string' => __('Product Code must be a Valid String'),
            'code.max' => __('Product Code may not exceed 255 Characters'),
            'code.unique' => __('This Product Code already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'has_variants.boolean' => __('Has Variants must be True or False'),
        ];
    }
}
