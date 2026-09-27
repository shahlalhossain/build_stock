<?php

namespace App\Http\Requests\UnitConversion;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreUnitConversionRequest.
 */
class StoreUnitConversionRequest extends FormRequest
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
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('product_units', 'id'),
                Rule::unique('unit_conversions')->where(fn ($query) => $query->where('product_id', $this->input('product_id'))),
            ],
            'factor_to_base' => ['required', 'numeric', 'min:0.000001'],
        ];
    }

    /**
     * A Conversion row is only meaningful for a Unit OTHER than the Product's own
     * base Unit — the base Unit's Factor is implicitly 1 and needs no row.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $productId = (int) $this->input('product_id');
            $unitId = (int) $this->input('unit_id');

            if (! $productId || ! $unitId) {
                return;
            }

            $product = Product::find($productId);

            if ($product && (int) $product->unit_id === $unitId) {
                $validator->errors()->add('unit_id', __('Selected Unit is already this Product\'s Base Unit — no Conversion is Needed.'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'product_id.required' => __('Product is Required'),
            'product_id.exists' => __('Selected Product does not Exist'),

            'unit_id.required' => __('Unit is Required'),
            'unit_id.exists' => __('Selected Unit does not Exist'),
            'unit_id.unique' => __('A Conversion for this Product and Unit already Exists'),

            'factor_to_base.required' => __('Conversion Factor is Required'),
            'factor_to_base.numeric' => __('Conversion Factor must be a Valid Number'),
            'factor_to_base.min' => __('Conversion Factor must be Greater than Zero'),
        ];
    }
}
