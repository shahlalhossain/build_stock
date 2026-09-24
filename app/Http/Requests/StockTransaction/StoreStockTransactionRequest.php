<?php

namespace App\Http\Requests\StockTransaction;

use App\Models\StockTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreStockTransactionRequest.
 */
class StoreStockTransactionRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(StockTransaction::USER_FACING_TYPES)],
            'store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            'supplier_id' => ['nullable', 'required_if:type,purchase', 'integer', Rule::exists('suppliers', 'id')],
            'destination_store_id' => ['nullable', 'required_if:type,transfer', 'integer', Rule::exists('stores', 'id'), 'different:store_id'],
            'transaction_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],

            // Variants are an Optional per-Item Breakdown (Setup Product Variants Modal).
            // Each Variant is one Attribute-Value Combination (e.g. Color:Red + Size:Small),
            // so attribute_value_ids carries every Attribute-Value id in that Combination.
            'items.*.variants' => ['nullable', 'array'],
            'items.*.variants.*.attribute_value_ids' => ['required', 'array', 'min:1'],
            'items.*.variants.*.attribute_value_ids.*' => ['integer', Rule::exists('attribute_values', 'id')],
            'items.*.variants.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.variants.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.variants.*.remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * When an Item carries a Variant Breakdown, its Variant Quantities must sum to
     * the Item's own Quantity — enforced here since it spans sibling array fields.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $variants = $item['variants'] ?? [];

                if (empty($variants)) {
                    continue;
                }

                $itemQuantity = (float) ($item['quantity'] ?? 0);
                $variantsTotal = array_sum(array_map(fn ($variant) => (float) ($variant['quantity'] ?? 0), $variants));

                if (abs($itemQuantity - $variantsTotal) > 0.01) {
                    $validator->errors()->add(
                        "items.{$index}.variants",
                        __('Variant Quantities (:variants_total) must Sum to the Item Quantity (:item_quantity).', [
                            'variants_total' => $variantsTotal,
                            'item_quantity' => $itemQuantity,
                        ])
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.required' => __('Type is Required'),
            'type.in' => __('Selected Type is Invalid'),

            'store_id.required' => __('Store is Required'),
            'store_id.integer' => __('Selected Store is Invalid'),
            'store_id.exists' => __('Selected Store does not Exist'),

            'supplier_id.required_if' => __('Supplier is Required for Purchase Transactions'),
            'supplier_id.integer' => __('Selected Supplier is Invalid'),
            'supplier_id.exists' => __('Selected Supplier does not Exist'),

            'destination_store_id.required_if' => __('Destination Store is Required for Transfer Transactions'),
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

            'items.*.quantity.required' => __('Quantity is Required for Every Line Item'),
            'items.*.quantity.numeric' => __('Quantity must be a Valid Number'),
            'items.*.quantity.not_in' => __('Quantity may not be Zero'),

            'items.*.unit_cost.numeric' => __('Unit Cost must be a Valid Number'),
            'items.*.unit_cost.min' => __('Unit Cost may not be Negative'),

            'items.*.remarks.string' => __('Line Remarks must be a Valid String'),

            'items.*.variants.*.attribute_value_ids.required' => __('Variant Selection is Invalid'),
            'items.*.variants.*.attribute_value_ids.array' => __('Variant Selection is Invalid'),
            'items.*.variants.*.attribute_value_ids.min' => __('Variant Selection is Invalid'),
            'items.*.variants.*.attribute_value_ids.*.integer' => __('Variant Selection is Invalid'),
            'items.*.variants.*.attribute_value_ids.*.exists' => __('Selected Variant Value does not Exist'),

            'items.*.variants.*.quantity.required' => __('Quantity is Required for Every Selected Variant'),
            'items.*.variants.*.quantity.numeric' => __('Variant Quantity must be a Valid Number'),
            'items.*.variants.*.quantity.not_in' => __('Variant Quantity may not be Zero'),

            'items.*.variants.*.unit_cost.numeric' => __('Variant Unit Cost must be a Valid Number'),
            'items.*.variants.*.unit_cost.min' => __('Variant Unit Cost may not be Negative'),

            'items.*.variants.*.remarks.string' => __('Variant Remarks must be a Valid String'),
        ];
    }
}
