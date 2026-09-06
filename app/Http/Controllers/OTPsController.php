<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OTPsController extends Controller
{
    public function index()
    {
        return true;
    }

    public function sendOTPToMobile(Request $request)
    {
        $mobileNumber   = null;
        $modelID        = $request->input('model_id');
        $modelName      = $request->input('model_name');

        $allowedModels = [
            'User'      => \App\Models\User::class,
            'Employee'  => \App\Models\Employee::class,
            'Member'    => \App\Models\Member::class,
        ];

        if (!isset($allowedModels[$modelName])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Model Name'
            ]);
        }

        if ($modelName) {
            $modelClass     = $allowedModels[$modelName];
            $modelObject    = $modelClass::findOrFail($modelID);

            if ($modelName === "Employee") {
                $mobileNumber   = $modelObject->mobile_personal;
            } else {
                $mobileNumber   = $modelObject->mobile;
            }
        } elseif ($request->has('mobile')) {
            $request->validate(['mobile' => ['required', 'regex:/^01\d{9}$/']]);
            $mobileNumber = $request->mobile;
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // TODO: Save OTP to DB -- otp_logs
        // TODO: Save a SMS Log -- sms_logs
        // TODO: Send OTP via SMS API

        $sentOTP = true;

        if ($sentOTP) {
            return response()->json([
                'code'      => 200,
                'otp'       => $otp,
                'mobile'    => $mobileNumber,
                'validity'  => 5,
                'otp_sent'  => true,
                'status'    => 'success',
                'message'   => 'Mobile OTP Sent Successfully'
            ]);
        } else {
            return response()->json([
                'code'      => 200,
                'otp'       => null,
                'mobile'    => $mobileNumber,
                'validity'  => null,
                'otp_sent'  => false,
                'status'    => 'failed',
                'message'   => 'Failed to Sent Mobile OTP'
            ]);
        }
    }

    public function sendOTPToEmail(Request $request)
    {

        $emailAddress   = null;
        $modelID        = $request->input('model_id');
        $modelName      = $request->input('model_name');

        $allowedModels = [
            'User'      => \App\Models\User::class,
            'Employee'  => \App\Models\Employee::class,
            'Member'    => \App\Models\Member::class,
        ];

        if (!isset($allowedModels[$modelName])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Model Name'
            ]);
        }

        if ($modelName) {
            $modelClass     = $allowedModels[$modelName];
            $modelObject    = $modelClass::findOrFail($modelID);

            if ($modelName === "Employee") {
                $emailAddress   = $modelObject->email_personal;
            } else {
                $emailAddress   = $modelObject->email;
            }
        } elseif ($request->has('email')) {
            $request->validate(['email' => ['required', 'email:rfc,dns']]);
            $request->validate(['email' => ['required', 'email:rfc,dns']], [
                    'email.required' => 'Email Address is Required',
                    'email.email'    => 'Provide a Valid Email Address',
                ]
            );
            $emailAddress = $request->email;
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // TODO: Save OTP to DB -- otp_logs
        // TODO: Save a OTP Email Log -- email_logs
        // TODO: Send OTP via SMS API

        return response()->json([
            'code'      => 200,
            'otp'       => $otp,
            'email'     => $emailAddress,
            'validity'  => 5,
            'otp_sent'  => true,
            'status'    => 'success',
            'message'   => 'Email OTP Sent Successfully'
        ]);
    }

    public function verifyMobileOTP(Request $request)
    {
//        $request->validate([
//            'mobile'    => ['required', 'regex:/^01\d{9}$/'],
//            'otp'       => ['required', 'digits:6']
//        ]);

        // TODO: OTP Verification Logic
        // TODO: Verify OTP from DB Table -- opt_logs
        $isValid = true;

        if ($isValid) {
            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'validate'  => true,
                'message'   => 'Validate Mobile OTP Successfully'
            ]);
        } else {
            return response()->json([
                'code'      => 200,
                'status'    => 'failed',
                'validate'  => false,
                'message'   => 'Incorrect OTP, Validation Failed'
            ]);
        }
    }

    public function verifyEmailOTP(Request $request)
    {
//        $request->validate([
//            'email'    => ['required', 'email:rfc,dns'],
//            'otp'       => ['required', 'digits:6']
//        ]);

        // TODO: OTP Verification Logic
        // TODO: Verify OTP from DB Table -- opt_logs
        $isValid = true;

        if ($isValid) {
            return response()->json([
                'code'      => 200,
                'status'    => 'success',
                'validate'  => true,
                'message'   => 'Validate Email OTP Successfully'
            ]);
        } else {
            return response()->json([
                'code'      => 200,
                'status'    => 'failed',
                'validate'  => false,
                'message'   => 'Incorrect OTP, Validation Failed'
            ]);
        }
    }
}
