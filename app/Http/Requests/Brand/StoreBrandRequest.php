<?php

namespace App\Http\Requests\Brand;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class StoreBrandRequest.
 */
class StoreBrandRequest extends FormRequest
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
            'type'          => ['required', Rule::in([User::TYPE_ADMIN, User::TYPE_USER])],
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
            'type.required'         => 'Brand type is Required',
            'type.in'               => 'Selected Brand Type is Invalid.',

            'guard_name.required'   => 'Guard Name is Required',
            'guard_name.string'     => 'Guard Name must be a Valid String',
            'guard_name.max'        => 'Guard Name may not exceed 255 Characters',

            'name.required'         => 'Brand Name is Required',
            'name.string'           => 'Brand Name must be a Valid String',
            'name.max'              => 'Brand Name may not exceed 255 Characters',
            'name.unique'           => 'This Brand already Exists for the Selected Guard',

            'description.string'    => 'Description must be a Valid String',
            'description.max'       => 'Description may not exceed 255 Characters',

            'parent_id.exists'      => 'Selected Parent Brand does not Exist',
        ];
    }
}
