<?php

namespace App\Http\Requests\ProductPurchase;

use App\Models\ProductPurchase;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class UpdateProductPurchaseRequest.
 */
class UpdateProductPurchaseRequest extends FormRequest
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
            'store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'transaction_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],

            'invoice_number' => ['required', 'string', 'max:255'],
            'supplier_invoice_date' => ['nullable', 'date'],
            'invoice_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'discount_type' => ['nullable', Rule::in(ProductPurchase::DISCOUNT_TYPES), 'required_with:discount_amount'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', Rule::in(ProductPurchase::PAYMENT_STATUSES)],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.product_variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.unit_id' => ['required', 'integer', Rule::exists('product_units', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * See StoreProductPurchaseRequest::withValidator for the shared logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('discount_type') === ProductPurchase::DISCOUNT_TYPE_PERCENTAGE
                && (float) $this->input('discount_amount', 0) > 100) {
                $validator->errors()->add('discount_amount', __('Percentage Discount may not Exceed 100.'));
            }

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

            'store_id.required' => __('Store is Required'),
            'store_id.integer' => __('Selected Store is Invalid'),
            'store_id.exists' => __('Selected Store does not Exist'),

            'supplier_id.required' => __('Supplier is Required'),
            'supplier_id.integer' => __('Selected Supplier is Invalid'),
            'supplier_id.exists' => __('Selected Supplier does not Exist'),

            'transaction_date.required' => __('Transaction Date is Required'),
            'transaction_date.date' => __('Transaction Date must be a Valid Date'),

            'remarks.string' => __('Remarks must be a Valid String'),

            'invoice_number.required' => __('Invoice Number is Required'),
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

            'items.*.product_variant_id.integer' => __('Selected Variant is Invalid'),
            'items.*.product_variant_id.exists' => __('Selected Variant does not Exist'),

            'items.*.unit_id.required' => __('Unit is Required for Every Line Item'),
            'items.*.unit_id.integer' => __('Selected Unit is Invalid'),
            'items.*.unit_id.exists' => __('Selected Unit does not Exist'),

            'items.*.quantity.required' => __('Quantity is Required for Every Line Item'),
            'items.*.quantity.numeric' => __('Quantity must be a Valid Number'),
            'items.*.quantity.min' => __('Quantity must be Greater than Zero'),

            'items.*.unit_cost.required' => __('Unit Cost is Required for Every Line Item'),
            'items.*.unit_cost.numeric' => __('Unit Cost must be a Valid Number'),
            'items.*.unit_cost.min' => __('Unit Cost may not be Negative'),

            'items.*.remarks.string' => __('Line Remarks must be a Valid String'),
        ];
    }
}
