<?php

return [
    'validation' => [
        'user' => [
            'type_required' => 'Please select a user type.',
            'type_in'       => 'Selected user type is invalid.',

            'name_required' => 'User name is required.',
            'name_string'   => 'User name must be a valid string.',
            'name_max'      => 'User name must not exceed 255 characters.',
            'name_regex'    => 'User name should not contain numbers.',

            'mobile_required'   => 'Mobile number is required.',
            'mobile_string'     => 'Mobile number must be a valid string.',
            'mobile_max'        => 'Mobile number must not exceed 20 characters.',

            'email_required'    => 'Email address is required.',
            'email_email'       => 'Please provide a valid email address.',
            'email_max'         => 'Email address must not exceed 255 characters.',
            'email_unique'      => 'This email address is already in use.',

            'password_required'     => 'Password is required.',
            'password_string'       => 'Password must be a valid string.',
            'password_min'          => 'Password must be at least 6 characters.',
            'password_confirmed'    => 'Password confirmation does not match.',
        ],
    ],
];
