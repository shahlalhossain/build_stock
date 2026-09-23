<?php

namespace App\Http\Requests\StockTransaction;

use App\Models\StockTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
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
        ];
    }
}
