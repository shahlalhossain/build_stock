<?php

namespace App\Http\Requests\ProductUnit;

use App\Models\ProductUnit;
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
            'group' => ['required', 'string', Rule::in(ProductUnit::GROUPS)],
            'name' => ['required', 'string', 'max:255', Rule::unique('product_units')],
            'symbol' => ['required', 'string', 'max:100', Rule::unique('product_units')],
            'description' => ['nullable', 'string', 'max:255'],
            'usage' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'group.required' => __('Group is Required'),
            'group.in' => __('Selected Group is Invalid'),

            'name.required' => __('Product-Unit Name is Required'),
            'name.string' => __('Product-Unit Name must be a Valid String'),
            'name.max' => __('Product-Unit Name may not exceed 255 Characters'),
            'name.unique' => __('This Product-Unit already Exists'),

            'symbol.required' => __('Symbol is Required'),
            'symbol.string' => __('Symbol must be a Valid String'),
            'symbol.max' => __('Symbol may not exceed 100 Characters'),
            'symbol.unique' => __('This Symbol already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'usage.string' => __('Usage must be a Valid String'),
            'usage.max' => __('Usage may not exceed 255 Characters'),
        ];
    }
}
