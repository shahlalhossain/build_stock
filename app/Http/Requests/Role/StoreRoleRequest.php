<?php

namespace App\Http\Requests\Role;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

/**
 * Class StorePermissionRequest.
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'type'          => ['required', Rule::in([User::TYPE_ADMIN, User::TYPE_MEMBER])],
            'guard_name'    => ['required', Rule::in(['web', 'api'])],
            'name'          => ['required', 'max:100', Rule::unique('roles')],
            'description'   => ['nullable', 'max:255'],

            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * @return array
     */
    public function messages() : array
    {
        return [
            'type.required'         => __('Role Type is Required'),
            'type.in'               => __('Selected Role Type is Invalid'),

            'guard_name.required'   => __('Guard is Required'),
            'guard_name.in'         => __('Selected Guard is Invalid'),

            'name.required'         => __('Role Name is Required'),
            'name.max'              => __('Role Name may not be greater than 100 Characters'),
            'name.unique'           => __('This Role Name has already been Taken'),

            'description.max'       => __('Description may not be greater than 255 Characters'),

            'permissions.array'     => __('Permissions must be Provided as an Array'),
            'permissions.*.integer' => __('Each Permission must be a Valid ID'),
            'permissions.*.exists'  => __('One or More Selected Permissions are Invalid'),
        ];
    }

//    protected function failedValidation(Validator $validator)
//    {
//        dd($validator->errors()->toArray());
//    }
}
