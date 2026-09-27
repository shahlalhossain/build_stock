<?php

namespace App\Http\Requests\ProductDelivery;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class UpdateProductDeliveryRequest.
 */
class UpdateProductDeliveryRequest extends FormRequest
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
            'store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            'delivered_to' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.product_variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.unit_id' => ['required', 'integer', Rule::exists('product_units', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * See StoreProductDeliveryRequest::withValidator for the shared logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $variantId = $item['product_variant_id'] ?? null;

                if ($productId && $variantId) {
                    $belongs = ProductVariant::where('product_id', $productId)->where('id', $variantId)->exists();

                    if (! $belongs) {
                        $validator->errors()->add(
                            "items.{$index}.product_variant_id",
                            __('Selected Variant does not Belong to the Selected Product.')
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'store_id.required' => __('Store is Required'),
            'store_id.integer' => __('Selected Store is Invalid'),
            'store_id.exists' => __('Selected Store does not Exist'),

            'delivered_to.string' => __('Delivered To must be a Valid String'),
            'delivered_to.max' => __('Delivered To may not Exceed 255 Characters'),

            'transaction_date.required' => __('Transaction Date is Required'),
            'transaction_date.date' => __('Transaction Date must be a Valid Date'),

            'remarks.string' => __('Remarks must be a Valid String'),

            'items.required' => __('At Least One Line Item is Required'),
            'items.array' => __('Line Items must be a Valid List'),
            'items.min' => __('At Least One Line Item is Required'),

            'items.*.product_id.required' => __('Product is Required for Every Line Item'),
            'items.*.product_id.integer' => __('Selected Product is Invalid'),
            'items.*.product_id.exists' => __('Selected Product does not Exist'),

            'items.*.product_variant_id.integer' => __('Selected Variant is Invalid'),
            'items.*.product_variant_id.exists' => __('Selected Variant does not Exist'),

            'items.*.unit_id.required' => __('Unit is Required for Every Line Item'),
            'items.*.unit_id.integer' => __('Selected Unit is Invalid'),
            'items.*.unit_id.exists' => __('Selected Unit does not Exist'),

            'items.*.quantity.required' => __('Quantity is Required for Every Line Item'),
            'items.*.quantity.numeric' => __('Quantity must be a Valid Number'),
            'items.*.quantity.min' => __('Quantity must be Greater than Zero'),

            'items.*.remarks.string' => __('Line Remarks must be a Valid String'),
        ];
    }
}
