<?php

namespace App\Http\Requests\ProductReceive;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductReceiveRequest.
 */
class UpdateProductReceiveRequest extends FormRequest
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
     *
     * transfer_id is intentionally NOT editable — a Receive stays tied to the
     * Transfer it was created against.
     */
    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.transfer_item_id' => ['required', 'integer', Rule::exists('product_transfer_items', 'id')],
            'items.*.unit_id' => ['required', 'integer', Rule::exists('product_units', 'id')],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.variance_remarks' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_date.required' => __('Transaction Date is Required'),
            'transaction_date.date' => __('Transaction Date must be a Valid Date'),

            'remarks.string' => __('Remarks must be a Valid String'),

            'items.required' => __('At Least One Line Item is Required'),
            'items.array' => __('Line Items must be a Valid List'),
            'items.min' => __('At Least One Line Item is Required'),

            'items.*.transfer_item_id.required' => __('Transfer Line is Required for Every Received Item'),
            'items.*.transfer_item_id.integer' => __('Selected Transfer Line is Invalid'),
            'items.*.transfer_item_id.exists' => __('Selected Transfer Line does not Exist'),

            'items.*.unit_id.required' => __('Unit is Required for Every Line Item'),
            'items.*.unit_id.integer' => __('Selected Unit is Invalid'),
            'items.*.unit_id.exists' => __('Selected Unit does not Exist'),

            'items.*.received_quantity.required' => __('Received Quantity is Required for Every Line Item'),
            'items.*.received_quantity.numeric' => __('Received Quantity must be a Valid Number'),
            'items.*.received_quantity.min' => __('Received Quantity must be Greater than Zero'),

            'items.*.variance_remarks.string' => __('Variance Remarks must be a Valid String'),
        ];
    }
}
