<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function profile(): View
    {
        return view('account.profile');
    }

    public function editProfile(): View
    {
        return view('account.edit_profile');
    }

    public function updateProfile(): View
    {
        return view('account.profile');
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'old_password' => ['required'],
                'password' => [
                    'required',
                    'confirmed',
                    'min:6',
                    'regex:/[A-Z]/',          // At least one uppercase letter
                    'regex:/[!@#$%^&*]/',      // At least one special character
                ],
            ], [
                'old_password.required' => 'Old Password is Required',
                'password.required' => 'Password is Required',
                'password.confirmed' => 'Confirm Password does not Match',
                'password.min' => 'Password must be at Least 6 Characters',
                'password.regex' => 'Password must Contain at Least One Uppercase Letter and One Special Character',
            ]);

            if (! Hash::check($validated['old_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['old_password' => ['Old Password is Incorrect']],
                ], 422);
            }

            $user->update(['password' => Hash::make($validated['password'])]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password has been Changed Successfully',
            ], 200);

        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again',
            ], 500);
        }
    }
}
