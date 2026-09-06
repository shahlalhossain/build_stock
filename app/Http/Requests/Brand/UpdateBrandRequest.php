<?php

namespace App\Http\Requests\Brand;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateBrandRequest.
 */
class UpdateBrandRequest extends FormRequest
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
        // If using route model binding
        $permissionId = $this->route('permission')?->id ?? $this->route('permission');

        return [
            'type'          => ['required', Rule::in([User::TYPE_ADMIN, User::TYPE_USER])],
            'guard_name'    => ['required', 'string', 'max:255'],
            'name'          => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permissionId)->where('guard_name', $this->guard_name)->whereNull('deleted_at')],
            'description'   => ['nullable', 'string', 'max:255'],
            'parent_id'     => ['nullable', 'exists:permissions,id', Rule::notIn([$permissionId])],
        ];
    }

    public function messages() : array
    {
        return [
            'type.required'       => 'Brand type is Required',
            'type.in'             => 'Selected Brand Type is Invalid.',

            'guard_name.required' => 'Guard Name is Required',
            'guard_name.string'   => 'Guard Name must be a Valid String',
            'guard_name.max'      => 'Guard Name may not exceed 255 Characters',

            'name.required'       => 'Brand Name is Required',
            'name.string'         => 'Brand Name must be a Valid String',
            'name.max'            => 'Brand Name may not exceed 255 Characters',
            'name.unique'         => 'This Brand already Exists for the Selected Guard',

            'description.string'  => 'Description must be a Valid String',
            'description.max'     => 'Description may not exceed 255 Characters',

            'parent_id.exists'    => 'Selected Parent Brand does not Exist',
            'parent_id.not_in'    => 'A Brand cannot be its own Parent',
        ];
    }
}
