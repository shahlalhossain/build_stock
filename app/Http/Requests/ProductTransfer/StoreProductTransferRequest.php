<?php

namespace App\Http\Requests\ProductTransfer;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreProductTransferRequest.
 */
class StoreProductTransferRequest extends FormRequest
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
            'requisition_id' => ['nullable', 'integer', Rule::exists('product_requisitions', 'id')],
            'source_store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            'destination_store_id' => ['required', 'integer', Rule::exists('stores', 'id'), 'different:source_store_id'],
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
     * Every selected product_variant_id must belong to that Item's own product_id.
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
            'requisition_id.integer' => __('Selected Requisition is Invalid'),
            'requisition_id.exists' => __('Selected Requisition does not Exist'),

            'source_store_id.required' => __('Source Store is Required'),
            'source_store_id.integer' => __('Selected Source Store is Invalid'),
            'source_store_id.exists' => __('Selected Source Store does not Exist'),

            'destination_store_id.required' => __('Destination Store is Required'),
            'destination_store_id.integer' => __('Selected Destination Store is Invalid'),
            'destination_store_id.exists' => __('Selected Destination Store does not Exist'),
            'destination_store_id.different' => __('Destination Store must be Different from the Source Store'),

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
