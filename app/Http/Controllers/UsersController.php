<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Exceptions\GeneralException;
use Throwable;
use Exception;

class UsersController extends Controller
{
    protected UserService $userService;
    protected ImageService $imageService;

    /**
     * UsersController Constructor
     * @param UserService $userService
     * @param ImageService $imageService
     */
    public function __construct(UserService $userService, ImageService $imageService)
    {
        $this->userService = $userService;
        $this->imageService = $imageService;
    }

    public function index(UsersDataTable $usersDataTable)
    {
        return $usersDataTable->render('user.index');
    }

    public function create() : View
    {
        return view('user.create');
    }

    public function store(StoreUserRequest $userRequest)
    {
        try {
            $data = $userRequest->validated();
            if ($userRequest->hasFile('profile_picture')) {
                $data['profile_picture'] = $userRequest->file('profile_picture');
            }
            $this->userService->createUser($data);
            return redirect()->route('user.index')->with('success', 'New User Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('User Creation Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating User: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show($id) : View
    {
        $data['user'] = User::withTrashed()->findOrFail($id);
        return view('user.show', $data);
    }

    public function download()
    {
        // TODO: Have to work on it
    }

    public function edit($id)
    {
        $data['user'] = User::findOrFail($id);
        return view('user.edit', $data);
    }

    public function update(UpdateUserRequest $userRequest, User $user)
    {
        try {
            $data = $userRequest->validated();
            if ($userRequest->hasFile('profile_picture')) {
                $data['profile_picture'] = $userRequest->file('profile_picture');
            }
            $data['remove_profile_picture'] = $userRequest->boolean('remove_profile_picture');
            $this->userService->updateUser($user, $data);
            return redirect()->route('user.index')->with('success', 'User Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('User Update Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating User: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function assignRoles(User $user) : View
    {
        $data['user']           = $user;
        $data['roles']          = Role::where('is_active', true)->where('id', '!=', 1)->with(['permissions'])->get();
        $data['assignedRoles']  = $user->roles->pluck('name')->toArray();;

        return view('user.assign-role', $data);
    }

    public function syncRoles(Request $request, User $user)
    {
        try {
            $request->validate([
                'roles'   => ['nullable', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ]);
            // Sync Roles
            $user->syncRoles($request->roles ?? []);
            return redirect()->route('admin.user.show', $user->id)->with('success', "Updated & Synced User's Roles Successfully");
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating User Roles: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Something went wrong while updating user roles');
        }
    }

    public function assignPermissions(User $user)
    {
        $data['user']               = $user;
        $data['userPermissions']    = $user->getAllPermissions()->pluck('name')->toArray();
        $data['groups']             = Permission::whereNull('parent_id')->whereHas('children')->with(['children'])->get();
        $data['nongroups']          = Permission::whereNull('parent_id')->whereDoesntHave('children')->whereNotIn('id', [1])->get();

        return view('user.assign-permission', $data);
    }

    public function syncPermissions(Request $request, User $user)
    {
        try {
            $request->validate([
                'permissions'   => ['nullable', 'array'],
                'permissions.*' => ['string', 'exists:permissions,name'],
            ]);
            // Sync Permissions
            $user->syncPermissions($request->permissions ?? []);
            return redirect()->route('admin.user.show', $user->id)->with('success', "Updated & Synced User's Permissions Successfully");
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating User Permissions: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Something went wrong while updating user permissions');
        }
    }

    public function changePassword(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'password' => [
                    'required',
                    'confirmed',
                    'min:6',
                    'regex:/[A-Z]/',          // At least one uppercase letter
                    'regex:/[!@#$%^&*]/'      // At least one special character
                ],
            ], [
                'password.required'  => 'Password is Required',
                'password.confirmed' => 'Confirm Password does not Match',
                'password.min'       => 'Password must be at Least 6 Characters',
                'password.regex'     => 'Password must Contain at Least One Uppercase Letter and One Special Character',
            ]);

            // Update Password
            $user->update(['password' => Hash::make($validated['password'])]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Password has been Changed Successfully',
            ], 200);

        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong. Please try again',
            ], 500);
        }
    }

    public function destroy($id) : JsonResponse
    {
        try {
            $this->userService->destroyUser($id);
            return response()->json(['success' => true, 'message' => 'User Deleted Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('User Not Found' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'User Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('User Deletion Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting User: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the User.'], 500);
        }
    }

    public function trash(UsersDataTable $usersDataTable)
    {
        $usersDataTable->showTrashed = true;
        return $usersDataTable->render('user.trashed');
    }

    public function restore($id) : JsonResponse
    {
        try {
            $this->userService->restoreUser($id);
            return response()->json(['success' => true, 'message' => 'User Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('User Not Found: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'User Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('User Restoration Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring User: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the User.'], 500);
        }
    }

    public function delete($id) : JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            return response()->json(['success' => true, 'message' => 'User Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete User.', 'error' => $exception->getMessage()], 500);
        }
    }
}
