<?php

namespace App\Http\Requests\Store;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateStoreRequest.
 */
class UpdateStoreRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(Store::TYPES)],
            'description' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'manager_ids' => ['nullable', 'array'],
            'manager_ids.*' => ['integer', Rule::exists('users', 'id')],
            'storekeeper_ids' => ['required', 'array', 'min:1'],
            'storekeeper_ids.*' => ['integer', Rule::exists('users', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.integer' => __('Selected Project is Invalid'),
            'project_id.exists' => __('Selected Project does not Exist'),

            'name.required' => __('Store Name is Required'),
            'name.string' => __('Store Name must be a Valid String'),
            'name.max' => __('Store Name may not exceed 255 Characters'),

            'type.required' => __('Type is Required'),
            'type.in' => __('Selected Type is Invalid'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'mobile.string' => __('Mobile must be a Valid String'),
            'mobile.max' => __('Mobile may not exceed 30 Characters'),

            'email.email' => __('Email must be a Valid Email Address'),
            'email.max' => __('Email may not exceed 150 Characters'),

            'manager_ids.array' => __('Selected Store Managers must be a Valid List'),
            'manager_ids.*.integer' => __('Selected Store Manager is Invalid'),
            'manager_ids.*.exists' => __('Selected Store Manager does not Exist'),

            'storekeeper_ids.required' => __('At Least One Storekeeper is Required'),
            'storekeeper_ids.array' => __('Selected Storekeepers must be a Valid List'),
            'storekeeper_ids.min' => __('At Least One Storekeeper is Required'),
            'storekeeper_ids.*.integer' => __('Selected Storekeeper is Invalid'),
            'storekeeper_ids.*.exists' => __('Selected Storekeeper does not Exist'),
        ];
    }
}
