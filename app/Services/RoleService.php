<?php

namespace App\Services;

use App\Events\Role\RoleCreated;
use App\Events\Role\RoleUpdated;
use App\Events\Role\RoleDestroyed;
use App\Events\Role\RoleRestored;
use App\Events\Role\RoleDeleted;
use App\Models\Permission;
use App\Models\Role;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeneralException;
use Exception;
use Throwable;

/**
 * Class RoleService.
 */
class RoleService extends BaseService
{
    /**
     * RoleService constructor.
     *
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    /**
     * @param array $data
     * @return Role
     *
     * @throws GeneralException
     * @throws \Throwable
     */
    public function storeRole(array $data = []) : Role
    {
        DB::beginTransaction();

        try {
            $roleData = [
                'type'          => $data['type'] ?? null,
                'guard_name'    => $data['guard_name'] ?? null,
                'name'          => $data['name'] ?? null,
                'description'   => $data['description'] ?? null,

                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ];

            $role = $this->model::create($roleData);
            //event(new RoleCreated($role));
            $permissionIDs = $data['permissions'] ?? [];
            if ($permissionIDs) {
                $this->assignPermissions($role, $permissionIDs);
            }
            DB::commit();
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Issue on Creating Role.'));
        }
        return $role;
    }

    /**
     * @param Role $role
     * @param array $data
     * @return Role
     *
     * @throws GeneralException
     * @throws \Throwable
     */
    public function updateRole(Role $role, array $data = []): Role
    {
        DB::beginTransaction();

        try {
            $role->update([
                'type'          => $data['type'] ?? null,
                'guard_name'    => $data['guard_name'] ?? null,
                'name'          => $data['name'] ?? null,
                'description'   => $data['description'] ?? null,
                'updated_by'    => Auth::id(),
            ]);

            $permissionIDs = $data['permissions'] ?? [];
            if ($permissionIDs) {
                $this->assignPermissions($role, $permissionIDs);
            }
            //event(new RoleUpdated($role));
            DB::commit();
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Role.'));
        }
        return $role;
    }

    /**
     * @param Role $role
     * @param array $permissionIDs
     * @return void
     * @throws GeneralException
     * @throws Throwable
     */
    public function assignPermissions(Role $role, array $permissionIDs) : void
    {
        DB::beginTransaction();

        try {
            if (empty($permissionIDs)) {
                $role->syncPermissions([]);
                DB::commit();
                return;
            }

            $permissions = Permission::whereIn('id', $permissionIDs)->get();

            if ($permissions->count() !== count($permissionIDs)) {
                throw new GeneralException('One or More Permissions are Invalid');
            }

            $role->syncPermissions($permissions);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $id
     * @return bool
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyRole($id) : bool
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail((int)$id);

            $role->is_active   = false;
            $role->deleted_by  = Auth::id();
            $role->deleted_at  = now();

            $result = $role->save();
            // event(new RoleDestroyed($role));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Role Destroy Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Role.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreRole($id) : bool
    {
        DB::beginTransaction();
        try {
            $role = Role::withTrashed()->findOrFail($id);
            $role->restore();
            $role->is_active = true;
            $role->deleted_by = null;

            $result = $role->save();
            // event(new RoleRestored($role));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Role Restore Failed: ' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Role.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteRole($id) : bool
    {
        DB::beginTransaction();
        try {
            $role = Role::withTrashed()->findOrFail($id);
            $role->users()->detach();
            $role->permissions()->detach();
            $result = $role->forceDelete();
            DB::commit();
            // event(new RoleDeleted($role));
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Role Permanent Deletion Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Role.'));
        }
    }
}
