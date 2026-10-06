<?php

namespace App\Http\Controllers;

use App\DataTables\ProfileActivityLogsDataTable;
use App\Models\LoginActivity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function profile(ProfileActivityLogsDataTable $activityLogsDataTable): JsonResponse|View
    {
        return $activityLogsDataTable->render('account.profile');
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

    public function logoutLoginActivity(Request $request, LoginActivity $loginActivity): JsonResponse
    {
        try {
            if ($loginActivity->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not Authorized to Perform this Action',
                ], 403);
            }

            if ($loginActivity->id === LoginActivity::currentIdFor($request->user(), $request)) {
                $this->endCurrentSession($request);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Session has been Logged Out Successfully',
                ], 200);
            }

            $loginActivity->update(['is_active' => false, 'logout_at' => now()]);
            $this->destroySessions($loginActivity->session_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Session has been Logged Out Successfully',
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again',
            ], 500);
        }
    }

    public function logoutAllLoginActivities(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->loginActivities()
                ->where('is_active', true)
                ->update(['is_active' => false, 'logout_at' => now()]);

            $this->destroySessions(null, $user->id, $request->session()->getId());

            $this->endCurrentSession($request);

            return response()->json([
                'status' => 'success',
                'message' => 'All Sessions have been Logged Out Successfully',
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again',
            ], 500);
        }
    }

    /**
     * Logs the user out but keeps the session id, so the login page can tell the
     * visitor their session expired (see redirectGuestsTo in bootstrap/app.php).
     */
    private function endCurrentSession(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->regenerateToken();
    }

    /**
     * Kills browser sessions for the database session driver: one by id, or all of a user's but one.
     */
    private function destroySessions(?string $sessionId, ?int $userId = null, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $query = DB::table(config('session.table', 'sessions'));

        if ($sessionId !== null) {
            $query->where('id', $sessionId)->delete();
        } elseif ($userId !== null) {
            $query->where('user_id', $userId)->where('id', '!=', $exceptSessionId)->delete();
        }
    }
}
