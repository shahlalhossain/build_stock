<?php

namespace App\Services;

use App\Models\User;
use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserDestroyed;
use App\Events\User\UserRestored;
use App\Events\User\UserUpdated;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\GeneralException;
use Exception;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class UserService.
 */
class UserService extends BaseService
{
    protected ImageService $imageService;

    public function __construct(User $user, ImageService $imageService)
    {
        $this->model = $user;
        $this->imageService = $imageService;
    }

    public function getByType($type, $perPage = false)
    {
        if (is_numeric($perPage)) {
            return $this->model::byType($type)->paginate($perPage);
        }

        return $this->model::byType($type)->get();
    }

    public function createUser(array $data = []) : User
    {
        DB::beginTransaction();

        try {
            // Handle - If Profile Picture is Present in the Request
            if (isset($data['profile_picture']) && $data['profile_picture'] instanceof UploadedFile) {
                $data['profile_picture_path'] = $this->imageService->uploadImage($data['profile_picture'], 'user.photo');
            }

            $user = $this->storeUser($data);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw new GeneralException(__('There was a Problem Creating User. Please Try Again.'));
        }
        //event(new UserCreated($user));
        DB::commit();

        return $user;
    }

    public function updateUser(User $user, array $data = []): User
    {
        DB::beginTransaction();

        try {
            // Remove Profile Picture
            if (!empty($data['remove_profile_picture']) && $user->profile_picture) {
                if (Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $data['profile_picture_path'] = null;
            }

            // Handle - If Profile Picture is Present in the Request
            if (isset($data['profile_picture']) && $data['profile_picture'] instanceof UploadedFile) {
                // If Exists Image or Profile Picture for this User then Delete the Old Image First
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $data['profile_picture_path'] = $this->imageService->uploadProfilePicture($data['profile_picture']);
            }

            $user->update([
                'type'              => $data['type'] ?? null,
                'name'              => $data['name'] ?? null,
                'mobile'            => $data['mobile'] ?? null,
                'email'             => $data['email'] ?? null,
                'profile_picture'   => $data['profile_picture_path'],
                'updated_by'        => Auth::id(),
            ]);
            //event(new UserUpdated($user));
            DB::commit();
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the User.'));
        }
        return $user;
    }

    public function updateProfile(User $user, array $data = []) : User
    {
        // TODO:
        $user->name = $data['name'] ?? null;

        if ($user->canChangeEmail() && $user->email !== $data['email']) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
            session()->flash('resent', true);
        }

        return tap($user)->save();
    }

    public function updatePassword(User $user, $data, $expired = false) : User
    {
        if (isset($data['current_password'])) {
            throw_if(
                ! Hash::check($data['current_password'], $user->password),
                new GeneralException(__('That is not your old password.'))
            );
        }

        // Reset the expiration clock
        if ($expired) {
            $user->password_changed_at = now();
        }

        $user->password = $data['password'];

        return tap($user)->update();
    }

    public function destroyUser($id) : bool
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail((int)$id);

            if ($user->id === auth()->id()) {
                throw new GeneralException(__('You Cannot Destroy Yourself.'));
            }

            $user->is_active   = false;
            $user->deleted_by  = Auth::id();
            $user->deleted_at  = now();

            $result = $user->save();
            // event(new UserDestroyed($user));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('User Destroy Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy User.'));
        }
    }

    public function restoreUser($id) : bool
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            $user->is_active = true;
            $user->deleted_by = null;

            $result = $user->save();
            // event(new UserRestored($user));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('User Restore Failed: ' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the User.'));
        }
    }

    public function deleteUser($id) : bool
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->findOrFail($id);

            // If Exists then Delete Profile Picture
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->roles()->detach();
            $user->permissions()->detach();
            $result = $user->forceDelete();
            DB::commit();
            // event(new UserDeleted($user));
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('User Permanent Deletion Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the User.'));
        }
    }

    protected function storeUser(array $data = []) : User
    {
        $userData = [
            'type'                  => $data['type'] ?? $this->model::TYPE_USER,
            'username'              => $this->generateNextUsername(),
            'name'                  => trim($data['name']) ?? null,
            'mobile'                => $this->formatMobileNumber($data['mobile']),
            'email'                 => trim($data['email']) ?? null,
            'password'              => trim($data['password']) ?? null,

            'profile_picture'       => $data['profile_picture_path'] ?? null,

            'is_mobile_verified'    => $data['is_mobile_verified'] ?? 0,
            'is_email_verified'     => $data['is_email_verified'] ?? 0,
            'mobile_verified_at'    => $data['mobile_verified_at'] ?? null,
            'email_verified_at'     => $data['email_verified_at'] ?? null,

            'is_active'             => true,
            'registration_platform' => 'CMS',
            'created_by'            => auth()->user()->id,
            'updated_by'            => auth()->user()->id,
            'timezone'              => 'Asia/Dhaka',
        ];

        return $this->model::create($userData);
    }

    public function generateNextUsername() : string
    {
        $maxUsername = User::max('username');
        if (!is_numeric($maxUsername)) {
            return '100000001';
        }
        return (string)((int)$maxUsername + 1);
    }

    public function formatMobileNumber($mobileNumber) : string
    {
        // TODO: Move it to Public Trait
        // Take Last 11 Digits
        return substr(preg_replace('/\D/', '', $mobileNumber), -11);
    }
}
