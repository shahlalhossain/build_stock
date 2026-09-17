<?php

namespace App\Http\Requests\Project;

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
            'slug' => ['required', 'string', 'max:255', Rule::unique('projects')],
            'description' => ['nullable', 'string', 'max:255'],
            'site_address' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'project_manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'priority_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Project Name is Required'),
            'name.string' => __('Project Name must be a Valid String'),
            'name.max' => __('Project Name may not exceed 255 Characters'),
            'name.unique' => __('This Project already Exists'),

            'slug.required' => __('Project Slug is Required'),
            'slug.string' => __('Project Slug must be a Valid String'),
            'slug.max' => __('Project Slug may not exceed 255 Characters'),
            'slug.unique' => __('This Project Slug already Exists'),

            'description.string' => __('Description must be a Valid String'),
            'description.max' => __('Description may not exceed 255 Characters'),

            'expected_end_date.after_or_equal' => __('Expected End Date must be On or After the Start Date'),

            'estimated_budget.numeric' => __('Estimated Budget must be a Valid Number'),
            'estimated_budget.min' => __('Estimated Budget must be 0 or Greater'),

            'project_manager_id.exists' => __('Selected Project Manager does not Exist'),

            'priority_order.numeric' => __('Priority Order must be a Valid Number'),
            'priority_order.min' => __('Priority Order must be 0 or Greater'),
        ];
    }
}
