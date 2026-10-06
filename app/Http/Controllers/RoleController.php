<?php

namespace App\Http\Controllers;

use App\DataTables\RolesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Role\PermissionAssignRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use App\Services\RoleService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class RoleController extends Controller
{
    protected RoleService $roleService;

    protected PermissionService $permissionService;

    /**
     * RoleController constructor.
     */
    public function __construct(RoleService $roleService, PermissionService $permissionService)
    {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }

    /**
     * @return mixed
     */
    public function index(RolesDataTable $rolesDataTable)
    {
        $rolesDataTable->showTrashed = false;

        return $rolesDataTable->render('role.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $data['unCategorizedPermissions'] = $this->permissionService->getUncategorizedPermissions();
        $data['categorizedPermissions'] = $this->permissionService->getCategorizedPermissions();
        $data['assignedPermissions'] = []; // This is Important to Manage Child-Permission Including in Both Case Create-Role, Edit-Role & Assign-Permissions

        return view('role.create', $data);
    }

    /**
     * @return RedirectResponse
     *
     * @throws Throwable
     */
    public function store(StoreRoleRequest $roleRequest)
    {
        try {
            $this->roleService->storeRole($roleRequest->validated());

            return redirect()->route('admin.role.index')->with('success', 'New Role Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Role Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Role: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View|Application|View|object
     */
    public function show(Role $role)
    {
        $permissionIDs = DB::table('role_has_permissions')->where('role_id', $role->id)->pluck('permission_id')->toArray();
        if ($permissionIDs) {
            $permissions = Permission::whereIn('id', $permissionIDs)->get();
        } else {
            $permissions = [];
        }
        $data['permissions'] = $permissions;
        $data['role'] = $role;

        return view('role.show', $data);
    }

    /**
     * @return mixed
     */
    public function edit(Role $role)
    {
        $data['role'] = $role;
        $data['unCategorizedPermissions'] = $this->permissionService->getUncategorizedPermissions();
        $data['categorizedPermissions'] = $this->permissionService->getCategorizedPermissions();
        $data['assignedPermissions'] = $role->permissions->pluck('id')->toArray();

        return view('role.edit', $data);
    }

    /**
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $roleRequest, Role $role)
    {
        try {
            $this->roleService->updateRole($role, $roleRequest->validated());

            return redirect()->route('admin.role.index')->with('success', 'Role Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Role Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Role: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function editPermissions($id): View
    {
        $role = Role::withTrashed()->with(['creator', 'updater', 'permissions'])->findOrFail($id);
        $data['role'] = $role;
        $data['unCategorizedPermissions'] = $this->permissionService->getUncategorizedPermissions();
        $data['categorizedPermissions'] = $this->permissionService->getCategorizedPermissions();
        $data['assignedPermissions'] = $role->permissions->pluck('id')->toArray();

        return view('role.assign-permission', $data);
    }

    /**
     * @return RedirectResponse
     */
    public function updatePermissions(PermissionAssignRequest $permissionAssignRequest, Role $role)
    {
        try {
            $this->roleService->assignPermissions($role, $permissionAssignRequest->validated()['permissions'] ?? []);

            return redirect()->route('admin.role.show', $role)->with('success', 'Permissions Assigned and/or Updated Successfully.');
        } catch (GeneralException $exception) {
            Log::warning('Permissions Assigned Failed: '.$exception->getMessage());

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Assigning Permission to this Role', ['exception' => $exception]);

            return back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->roleService->destroyRole($id);

            return response()->json(['success' => true, 'message' => 'Role Deleted Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Role Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Role Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Role Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Role: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Role.'], 500);
        }
    }

    /**
     * @return mixed
     */
    public function trash(RolesDataTable $roleDataTable)
    {
        $roleDataTable->showTrashed = true;

        return $roleDataTable->render('role.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->roleService->restoreRole($id);

            return response()->json(['success' => true, 'message' => 'Role Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Role Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Role Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Role Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Role: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Role.'], 500);
        }
    }

    /**
     * @throws Throwable
     */
    public function delete($id): JsonResponse
    {
        try {
            $this->roleService->deleteRole($id);

            return response()->json(['success' => true, 'message' => 'Role Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Role.', 'error' => $exception->getMessage()], 500);
        }
    }
}
