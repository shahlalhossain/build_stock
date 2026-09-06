<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class PermissionAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules() : array
    {
        return [
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    public function messages() : array
    {
        return [
            'permissions.array'     => 'Permissions must be Provided as an Array',
            'permissions.*.integer' => 'Each Permission must be a Valid ID',
            'permissions.*.exists'  => 'One or More Selected Permissions are Invalid',
        ];
    }
}
