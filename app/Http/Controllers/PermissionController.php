<?php

namespace App\Http\Controllers;

use App\DataTables\PermissionsDataTable;
use App\Http\Requests\Permission\StoreBrandRequest;
use App\Http\Requests\Permission\UpdateBrandRequest;
use App\Models\Permission;
use Illuminate\View\View;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeneralException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

/**
 * Class PermissionController.
 */
class PermissionController
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(PermissionsDataTable $permissionsDataTable)
    {
        $permissionsDataTable->showTrashed = false;
        return $permissionsDataTable->render('permission.index');
    }

    public function create()
    {
        $data['permissions'] = Permission::whereNull('parent_id')->where('id', '!=', 1)->get();
        return view('permission.create', $data);
    }

    public function store(StoreBrandRequest $permissionRequest)
    {
        try {
            $this->permissionService->storePermission($permissionRequest->validated());
            return redirect()->route('admin.permission.index')->with('success', 'New Permission Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Permission Creation Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Permission: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Permission $permission)
    {
        $data['permission'] = $permission->load(['creator', 'updater', 'deleter']);
        return view('permission.show', $data);
    }

    public function edit(Permission $permission) : View
    {
        $data['permission'] = $permission;
        $data['parentPermissions'] = Permission::whereNull('parent_id')->where('id', '!=', 1)->get();
        return view('permission.edit', $data);
    }

    public function update(UpdateBrandRequest $permissionRequest, Permission $permission) : RedirectResponse
    {
        try {
            $this->permissionService->updatePermission($permission, $permissionRequest->validated());
            return redirect()->route('admin.permission.index')->with('success', 'Permission Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Permission Update Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Permission: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id) : JsonResponse
    {
        try {
            $this->permissionService->destroyPermission($id);
            return response()->json(['success' => true, 'message' => 'Permission Deleted Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Permission Not Found' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Permission Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Permission Deletion Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Permission: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Permission.'], 500);
        }
    }

    public function trash(PermissionsDataTable $permissionsDataTable)
    {
        $permissionsDataTable->showTrashed = true;
        return $permissionsDataTable->render('permission.trashed');
    }

    public function restore($id) : JsonResponse
    {
        try {
            $this->permissionService->restorePermission($id);
            return response()->json(['success' => true, 'message' => 'Permission Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Permission Not Found: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Permission Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Permission Restoration Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Permission: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Permission.'], 500);
        }
    }

    public function delete($id) : JsonResponse
    {
        try {
            $this->permissionService->deletePermission($id);
            return response()->json(['success' => true, 'message' => 'Permission Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete Permission.', 'error' => $exception->getMessage()], 500);
        }
    }
}
