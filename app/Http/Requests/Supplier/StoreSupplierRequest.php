<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Class StoreSupplierRequest.
 */
class StoreSupplierRequest extends FormRequest
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
            // TODO: validate against supplier_types once that migration/table is provided.
            'supplier_type_id' => ['required', 'integer'],

            'code' => ['required', 'string', 'max:30', Rule::unique('suppliers')],
            'name' => ['required', 'string', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:50'],
            'bin_number' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],

            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.name' => ['required', 'string', 'max:150'],
            'contacts.*.designation' => ['nullable', 'string', 'max:100'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.mobile' => ['nullable', 'string', 'max:30'],
            'contacts.*.contact_type' => ['nullable', 'string', 'max:50'],
            'contacts.*.remarks' => ['nullable', 'string'],
            'contacts.*.is_primary' => ['nullable', 'boolean'],

            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.address_type' => ['required', 'string', 'max:255'],
            'addresses.*.address' => ['required', 'string', 'max:255'],
            'addresses.*.address_bn' => ['nullable', 'string', 'max:255'],
            'addresses.*.division_name' => ['nullable', 'string', 'max:255'],
            'addresses.*.district_name' => ['nullable', 'string', 'max:255'],
            'addresses.*.thana_name' => ['nullable', 'string', 'max:255'],

            'payment_accounts' => ['nullable', 'array'],
            'payment_accounts.*.payment_method' => ['required_with:payment_accounts.*.account_number', 'nullable', 'string', 'max:50'],
            'payment_accounts.*.account_name' => ['required_with:payment_accounts.*.account_number', 'nullable', 'string', 'max:150'],
            'payment_accounts.*.account_number' => ['nullable', 'string', 'max:100'],
            'payment_accounts.*.bank_name' => ['required_with:payment_accounts.*.account_number', 'nullable', 'string', 'max:150'],
            'payment_accounts.*.branch_name' => ['required_with:payment_accounts.*.account_number', 'nullable', 'string', 'max:150'],
            'payment_accounts.*.routing_number' => ['nullable', 'string', 'max:50'],
            'payment_accounts.*.remarks' => ['nullable', 'string'],
            'payment_accounts.*.is_primary' => ['nullable', 'boolean'],

            'mfs_accounts' => ['nullable', 'array'],
            'mfs_accounts.*.mfs_operator_name' => ['required_with:mfs_accounts.*.mfs_account_number', 'nullable', 'string', 'max:50'],
            'mfs_accounts.*.mfs_account_number' => ['nullable', 'string', 'max:30'],
            'mfs_accounts.*.remarks' => ['nullable', 'string'],
            'mfs_accounts.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_type_id.required' => __('Supplier Type is Required'),

            'code.required' => __('Supplier Code is Required'),
            'code.unique' => __('This Supplier Code already Exists'),

            'name.required' => __('Supplier Name is Required'),
            'name.max' => __('Supplier Name may not exceed 255 Characters'),

            'contacts.required' => __('At least one Contact is Required'),
            'contacts.min' => __('At least one Contact is Required'),
            'contacts.*.name.required' => __('Contact Name is Required'),
            'contacts.*.email.email' => __('Contact Email must be a Valid Email Address'),

            'addresses.required' => __('At least one Address is Required'),
            'addresses.min' => __('At least one Address is Required'),
            'addresses.*.address_type.required' => __('Address Type is Required'),
            'addresses.*.address.required' => __('Address is Required'),

            'payment_accounts.*.payment_method.required_with' => __('Payment Method is Required when an Account Number is given'),
            'payment_accounts.*.account_name.required_with' => __('Account Name is Required when an Account Number is given'),
            'payment_accounts.*.bank_name.required_with' => __('Bank Name is Required when an Account Number is given'),
            'payment_accounts.*.branch_name.required_with' => __('Branch Name is Required when an Account Number is given'),

            'mfs_accounts.*.mfs_operator_name.required_with' => __('MFS Operator is Required when an Account Number is given'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->ensureAtMostOnePrimary($validator, 'contacts', __('Only one Contact can be marked Primary'));
            $this->ensureAtMostOnePrimary($validator, 'payment_accounts', __('Only one Payment Account can be marked Primary'));
            $this->ensureAtMostOnePrimary($validator, 'mfs_accounts', __('Only one MFS Account can be marked Primary'));
        });
    }

    protected function ensureAtMostOnePrimary(Validator $validator, string $field, string $message): void
    {
        $rows = (array) $this->input($field, []);

        $primaryCount = collect($rows)->filter(function ($row) {
            return filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN);
        })->count();

        if ($primaryCount > 1) {
            $validator->errors()->add($field, $message);
        }
    }
}
