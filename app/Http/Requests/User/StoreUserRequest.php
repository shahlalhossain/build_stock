<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use Illuminate\Validation\Rules\Password;

/**
 * Class StoreUserRequest.
 */
class StoreUserRequest extends FormRequest
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
        return [
            'type'                  => ['required', Rule::in([User::TYPE_ADMIN, User::TYPE_USER, User::TYPE_MEMBER])],
            'name'                  => ['required', 'string', 'max:255', 'regex:/^[^\d]*$/'],
            'mobile'                => ['required', 'regex:/^01\d{9}$/', Rule::unique('users')],
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users')],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],

            'is_mobile_verified'    => ['nullable'],
            'is_email_verified'     => ['nullable'],
            'profile_picture'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array
     */
    public function messages() : array
    {
        return [
            'type.required'         => __('Please Select a User Type'),
            'type.in'               => __('Selected User Type is Invalid'),

            'name.required'         => __('Name is Required'),
            'name.string'           => __('Name must be Valid String'),
            'name.max'              => __('Name must not Exceed 255 Characters'),
            'name.regex'            => __('Name should not Contain Numbers'),

            'mobile.required'       => __('Mobile Number is Required'),
            'mobile.regex'          => __('Mobile number must be 11 digits and start with 01'),
            'mobile.unique'         => __('Mobile Number is already Exists'),

            'email.required'        => __('Email Address is Required'),
            'email.email'           => __('Please Provide a Valid Email Address'),
            'email.max'             => __('Email Address must NOT Exceed 255 Characters'),
            'email.unique'          => __('This Email Address is Already Used'),

            'password.required'     => __('Password is Required'),
            'password.string'       => __('Password must be a Valid String'),
            'password.min'          => __('Password must be at least 6 Characters'),
            'password.confirmed'    => __('Password Confirmation Does NOT Matched'),

            'profile_picture.image' => __('Profile Picture must be a Valid Image file (jpg, png, jpeg)'),
            'profile_picture.max'   => __('Profile Picture must not Exceed 10MB in Size'),
        ];
    }
}
