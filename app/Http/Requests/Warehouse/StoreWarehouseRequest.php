<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreWarehouseRequest.
 */
class StoreWarehouseRequest extends FormRequest
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
            // project_id absent/empty means Head Office; a real id means that Project (Site).
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses')],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.integer' => __('Selected Project is Invalid'),
            'project_id.exists' => __('Selected Project does not Exist'),

            'name.required' => __('Warehouse Name is Required'),
            'name.string' => __('Warehouse Name must be a Valid String'),
            'name.max' => __('Warehouse Name may not exceed 255 Characters'),

            'code.required' => __('Warehouse Code is Required'),
            'code.string' => __('Warehouse Code must be a Valid String'),
            'code.max' => __('Warehouse Code may not exceed 50 Characters'),
            'code.unique' => __('This Warehouse Code already Exists'),
        ];
    }
}
