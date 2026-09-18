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
        $store = $this->route('store');

        return [
            // project_id absent/empty means Head Office; a real id means that Project (Site).
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('stores', 'code')->ignore($store)],
            'type' => ['required', 'string', Rule::in(Store::TYPES)],
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

            'code.required' => __('Store Code is Required'),
            'code.string' => __('Store Code must be a Valid String'),
            'code.max' => __('Store Code may not exceed 50 Characters'),
            'code.unique' => __('This Store Code already Exists'),

            'type.required' => __('Type is Required'),
            'type.in' => __('Selected Type is Invalid'),
        ];
    }
}
