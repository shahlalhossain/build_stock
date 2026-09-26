<?php

namespace App\Http\Requests\StockTransaction;

use App\Models\ProductVariant;
use App\Models\StockTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class UpdateStockTransactionRequest.
 */
class UpdateStockTransactionRequest extends FormRequest
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

            // Purchase-only Fields.
            'invoice_number' => ['nullable', 'required_if:type,purchase', 'string', 'max:255'],
            'supplier_invoice_date' => ['nullable', 'date'],
            'invoice_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'discount_type' => ['nullable', Rule::in(StockTransaction::DISCOUNT_TYPES), 'required_with:discount_amount'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', Rule::in(StockTransaction::PAYMENT_STATUSES)],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],

            // Variants are an Optional per-Item Breakdown (Setup Product Variants Modal).
            // Each Variant Row picks one of the Product's existing product_variants.
            'items.*.variants' => ['nullable', 'array'],
            'items.*.variants.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.variants.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.variants.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.variants.*.remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Cross-field rules that span sibling array fields — see
     * StoreStockTransactionRequest::withValidator for the shared logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('discount_type') === StockTransaction::DISCOUNT_TYPE_PERCENTAGE
                && (float) $this->input('discount_amount', 0) > 100) {
                $validator->errors()->add('discount_amount', __('Percentage Discount may not Exceed 100.'));
            }

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

                $productId = (int) ($item['product_id'] ?? 0);
                $variantIds = array_filter(array_column($variants, 'product_variant_id'));

                if ($productId && $variantIds) {
                    $validCount = ProductVariant::query()
                        ->where('product_id', $productId)
                        ->whereIn('id', $variantIds)
                        ->count();

                    if ($validCount !== count(array_unique($variantIds))) {
                        $validator->errors()->add(
                            "items.{$index}.variants",
                            __('One or more Selected Variants do not Belong to the Selected Product.')
                        );
                    }
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

            'invoice_number.required_if' => __('Invoice Number is Required for Purchase Transactions'),
            'invoice_number.string' => __('Invoice Number must be a Valid String'),
            'invoice_number.max' => __('Invoice Number may not Exceed 255 Characters'),

            'supplier_invoice_date.date' => __('Supplier Invoice Date must be a Valid Date'),

            'invoice_attachment.file' => __('Invoice Attachment must be a Valid File'),
            'invoice_attachment.mimes' => __('Invoice Attachment must be a PDF, JPG or PNG File'),
            'invoice_attachment.max' => __('Invoice Attachment may not Exceed 10 MB'),

            'discount_type.in' => __('Selected Discount Type is Invalid'),
            'discount_type.required_with' => __('Discount Type is Required when a Discount Amount is Given'),

            'discount_amount.numeric' => __('Discount Amount must be a Valid Number'),
            'discount_amount.min' => __('Discount Amount may not be Negative'),

            'tax_amount.numeric' => __('Tax Amount must be a Valid Number'),
            'tax_amount.min' => __('Tax Amount may not be Negative'),

            'payment_status.in' => __('Selected Payment Status is Invalid'),

            'paid_amount.numeric' => __('Paid Amount must be a Valid Number'),
            'paid_amount.min' => __('Paid Amount may not be Negative'),

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

            'items.*.variants.*.product_variant_id.required' => __('Variant Selection is Invalid'),
            'items.*.variants.*.product_variant_id.integer' => __('Variant Selection is Invalid'),
            'items.*.variants.*.product_variant_id.exists' => __('Selected Variant does not Exist'),

            'items.*.variants.*.quantity.required' => __('Quantity is Required for Every Selected Variant'),
            'items.*.variants.*.quantity.numeric' => __('Variant Quantity must be a Valid Number'),
            'items.*.variants.*.quantity.not_in' => __('Variant Quantity may not be Zero'),

            'items.*.variants.*.unit_cost.numeric' => __('Variant Unit Cost must be a Valid Number'),
            'items.*.variants.*.unit_cost.min' => __('Variant Unit Cost may not be Negative'),

            'items.*.variants.*.remarks.string' => __('Variant Remarks must be a Valid String'),
        ];
    }
}
