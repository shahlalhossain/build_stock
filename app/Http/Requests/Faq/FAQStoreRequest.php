<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FAQStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'faq_category_id'   => 'required|exists:faq_categories,id',
            'question'          => 'required|string|max:255|unique:faqs,question',
            'answer'            => 'required|string|max:255',
            'language'          => 'nullable|string|in:Bangla,English',
        ];
    }

    public function messages() : array
    {
        return [
            'faq_category_id.required'  => __('Category is Required'),
            'faq_category_id.exists'    => __('Selected Category is Invalid'),

            'question.required'         => __('Question is Required'),
            'question.unique'           => __('This Question already been Taken'),
            'question.string'           => __('Question must be a valid String'),
            'question.max'              => __('Question may not Exceed 255 Characters'),

            'answer.required'           => __('Answer is Required'),
            'answer.string'             => __('Answer must be a Valid String'),
            'answer.max'                => __('Answer may not Exceed 255 Characters'),

            'language.string'           => __('Language must be a Valid String'),
            'language.in'               => __('Language must be either Bangla or English'),
        ];
    }
}
