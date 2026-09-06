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
        $brand = $this->route('brand');

        return [
            'name'              => [ 'required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand)],
            'slug'              => [ 'required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand)],
            'description'       => [ 'nullable', 'string', 'max:255', ],
            'priority_order'    => [ 'nullable', 'integer', 'min:0', ],
        ];
    }

    public function messages() : array
    {
        return [
            'name.required'             => __('Brand Name is Required'),
            'name.string'               => __('Brand Name must be a Valid String'),
            'name.max'                  => __('Brand Name may not exceed 255 Characters'),
            'name.unique'               => __('This Brand already Exists'),

            'slug.required'             => __('Brand Slug is Required'),
            'slug.string'               => __('Brand Slug must be a Valid String'),
            'slug.max'                  => __('Brand Slug may not exceed 255 Characters'),
            'slug.unique'               => __('This Brand Slug already Exists'),

            'description.string'        => __('Description must be a Valid String'),
            'description.max'           => __('Description may not exceed 255 Characters'),

            'priority_order.integer'    => __('Priority Order must be a Valid Number'),
            'priority_order.min'        => __('Priority Order must be 0 or greater'),
        ];
    }
}
