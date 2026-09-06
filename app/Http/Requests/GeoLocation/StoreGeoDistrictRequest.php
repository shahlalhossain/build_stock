<?php

namespace App\Http\Requests\GeoLocation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeoDistrictRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules() : array
    {
        return [
            'division_id'   => ['required', 'numeric', Rule::exists('location_divisions', 'id')],
            'name_en'       => ['required', 'min:3', 'max:100', 'not_regex:/[0-9]/', Rule::unique('location_districts', 'name_en')],
            'name_bn'       => ['nullable', 'min:3', 'max:100', 'not_regex:/[0-9]/', Rule::unique('location_districts', 'name_bn')],
        ];
    }

    /**
     * @return string[]
     */
    public function messages() : array
    {
        return [
            'division_id.required'  => 'Division is Required.',
            'division_id.numeric'   => 'Division ID must be Numeric.',
            'division_id.exists'    => 'Select Valid Division.',

            'name_en.required'  => 'The District Name is Required.',
            'name_en.min'       => 'The District Name must be at least 3 Characters.',
            'name_en.max'       => 'Limit Name to 100 Characters.',
            'name_en.not_regex' => 'The District Name must NOT Contain Numbers.',
            'name_en.unique'    => 'The District Name has Already been Taken.',

            'name_bn.min'       => 'The District Name-Bangla must be at least 3 characters.',
            'name_bn.max'       => 'Limit Bangla Name to 100 Characters.',
            'name_bn.not_regex' => 'The District Name-Bangla must NOT Contain Numbers.',
            'name_bn.unique'    => 'The District Name-Bangla has Already been Taken.',
        ];
    }
}
