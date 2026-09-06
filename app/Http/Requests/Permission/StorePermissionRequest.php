<?php

namespace App\Http\Requests\Permission;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class StorePermissionRequest.
 */
class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
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
            'guard_name'    => ['required', 'string', 'max:255',],
            'name'          => ['required', 'string', 'max:255', Rule::unique('permissions')],
            'description'   => ['nullable', 'string', 'max:255'],
            'parent_id'     => ['nullable', 'exists:permissions,id'],
        ];
    }

    /**
     * @return array
     */
    public function messages() : array
    {
        return [
            'type.required'         => 'Permission type is Required',
            'type.in'               => 'Selected Permission Type is Invalid.',

            'guard_name.required'   => 'Guard Name is Required',
            'guard_name.string'     => 'Guard Name must be a Valid String',
            'guard_name.max'        => 'Guard Name may not exceed 255 Characters',

            'name.required'         => 'Permission Name is Required',
            'name.string'           => 'Permission Name must be a Valid String',
            'name.max'              => 'Permission Name may not exceed 255 Characters',
            'name.unique'           => 'This Permission already Exists for the Selected Guard',

            'description.string'    => 'Description must be a Valid String',
            'description.max'       => 'Description may not exceed 255 Characters',

            'parent_id.exists'      => 'Selected Parent Permission does not Exist',
        ];
    }
}
