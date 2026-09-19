<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreProjectRequest.
 */
class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('projects')],
            'description' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'project_manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'current_state' => ['required', Rule::in(Project::CURRENT_STATES)],

            // Site Address (saved into the polymorphic addresses table)
            'division_id' => ['required', 'integer', Rule::exists('location_divisions', 'id')],
            'district_id' => ['required', 'integer', Rule::exists('location_districts', 'id')],
            'thana_id' => ['required', 'integer', Rule::exists('location_upazilas', 'id')],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_address' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Project Name is Required'),
            'name.string' => __('Project Name must be a Valid String'),
            'name.max' => __('Project Name may not exceed 255 Characters'),
            'name.unique' => __('This Project already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'expected_end_date.after_or_equal' => __('Expected End Date must be On or After the Start Date'),

            'estimated_budget.numeric' => __('Estimated Budget must be a Valid Number'),
            'estimated_budget.min' => __('Estimated Budget must be 0 or Greater'),

            'project_manager_id.exists' => __('Selected Project Manager does not Exist'),

            'current_state.required' => __('Current State is Required'),
            'current_state.in' => __('Selected Current State is Invalid'),

            'division_id.required' => __('Division is Required'),
            'division_id.exists' => __('Selected Division does not Exist'),

            'district_id.required' => __('District is Required'),
            'district_id.exists' => __('Selected District does not Exist'),

            'thana_id.required' => __('Thana/Upazila is Required'),
            'thana_id.exists' => __('Selected Thana/Upazila does not Exist'),

            'address.required' => __('Address is Required'),

            'latitude.numeric' => __('Latitude must be a Valid Number'),
            'latitude.between' => __('Latitude must be Between -90 and 90'),

            'longitude.numeric' => __('Longitude must be a Valid Number'),
            'longitude.between' => __('Longitude must be Between -180 and 180'),
        ];
    }
}
