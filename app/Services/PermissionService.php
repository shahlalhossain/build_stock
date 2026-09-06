<?php

namespace App\Services;

use App\Events\Permission\BranCreated;
use App\Events\Permission\BrandUpdated;
use App\Events\Permission\BrandDeleted;
use App\Models\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeneralException;
use Exception;
use Throwable;

/**
 * Class PermissionService.
 */
class PermissionService extends BaseService
{
    /**
     * PermissionService Constructor.
     *
     * @param Permission $permission
     */
    public function __construct(Permission $permission)
    {
        $this->model = $permission;
    }

    /**
     * @return mixed
     */
    public function getCategorizedPermissions()
    {
        return $this->model::isMaster()
            ->with('children')
            ->get();
    }

    /**
     * @return mixed
     */
    public function getUncategorizedPermissions()
    {
        return $this->model::singular()
            ->where('id', '!=', 1) //Or
            ->whereKeyNot(1) //Or
            ->where('name', '!=', 'All Permissions') //Or
            ->whereNotIn('name', ['All Permissions'])
            ->orderBy('sort', 'asc')
            ->get();
    }

    public function storePermission(array $data = []) : Permission
    {
        DB::beginTransaction();
        try {
            $permissionData = [
                'type'          => $data['type'] ?? null,
                'guard_name'    => $data['guard_name'] ?? null,
                'name'          => $data['name'] ?? null,
                'description'   => $data['description'] ?? null,
                'parent_id'     => $data['parent_id'] ?? null,
                'is_active'     => true,
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ];
            $permission = $this->model::create($permissionData);
            DB::commit();
            //event(new PermissionCreated($permission));
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating the Permission.'));
        }
        return $permission;
    }

    public function updatePermission(Permission $permission, array $data = []) : Permission
    {
        DB::beginTransaction();

        try {
            $permission->update([
                'type'          => $data['type'] ?? null,
                'guard_name'    => $data['guard_name'] ?? null,
                'name'          => $data['name'] ?? null,
                'description'   => $data['description'] ?? null,
                'parent_id'     => $data['parent_id'] ?? null,
                'updated_by'    => Auth::id(),
            ]);

            DB::commit();

        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Permission.'));
        }

        return $permission;
    }

    /**
     * @param $id
     * @return bool
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyPermission($id) : bool
    {
        DB::beginTransaction();

        try {
            $permission = Permission::findOrFail((int)$id);

            $permission->is_active   = false;
            $permission->deleted_by  = Auth::id();
            $permission->deleted_at  = now();

            $result = $permission->save();
            // event(new PermissionDestroyed($permission));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Permission Destroy Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Permission.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function restorePermission($id) : bool
    {
        DB::beginTransaction();
        try {
            $permission = Permission::withTrashed()->findOrFail($id);
            $permission->restore();
            $permission->is_active = true;
            $permission->deleted_by = null;

            $result = $permission->save();
            // event(new PermissionRestored($permission));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Permission Restore Failed: ' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Permission.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function deletePermission($id) : bool
    {
        DB::beginTransaction();
        try {
            $permission = Permission::withTrashed()->findOrFail($id);
            $permission->roles()->detach();
            $permission->users()->detach();
            $result = $permission->forceDelete();
            DB::commit();
            // event(new PermissionDeleted($permission));
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Permission Permanent Deletion Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Permission.'));
        }
    }
}
