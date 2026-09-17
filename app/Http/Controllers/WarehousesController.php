<?php

namespace App\Http\Controllers;

use App\DataTables\WarehousesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Project;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WarehousesController extends Controller
{
    protected WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function index(WarehousesDataTable $warehousesDataTable)
    {
        $warehousesDataTable->showTrashed = false;

        return $warehousesDataTable->render('warehouse.index');
    }

    public function create()
    {
        $data['projects'] = Project::query()->where('is_active', true)->orderBy('name')->get();

        return view('warehouse.create', $data);
    }

    public function store(StoreWarehouseRequest $warehouseRequest)
    {
        try {
            $this->warehouseService->storeWarehouse($warehouseRequest->validated());

            return redirect()->route('warehouse.index')->with('success', 'New Warehouse Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Warehouse Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Warehouse: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Warehouse $warehouse)
    {
        $data['warehouse'] = $warehouse->load(['project', 'creator', 'updater', 'deleter']);

        return view('warehouse.show', $data);
    }

    public function edit(Warehouse $warehouse): View
    {
        $data['warehouse'] = $warehouse;
        $data['projects'] = Project::query()->where('is_active', true)->orderBy('name')->get();

        return view('warehouse.edit', $data);
    }

    public function update(UpdateWarehouseRequest $warehouseRequest, Warehouse $warehouse): RedirectResponse
    {
        try {
            $this->warehouseService->updateWarehouse($warehouse, $warehouseRequest->validated());

            return redirect()->route('warehouse.index')->with('success', 'Warehouse Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Warehouse Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Warehouse: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->warehouseService->destroyWarehouse($id);

            return response()->json(['success' => true, 'message' => 'Warehouse Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Warehouse Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Warehouse Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Warehouse Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Warehouse: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Warehouse.'], 500);
        }
    }

    public function trash(WarehousesDataTable $warehousesDataTable)
    {
        $warehousesDataTable->showTrashed = true;

        return $warehousesDataTable->render('warehouse.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->warehouseService->restoreWarehouse($id);

            return response()->json(['success' => true, 'message' => 'Warehouse Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Warehouse Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Warehouse Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Warehouse Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Warehouse: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Warehouse.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->warehouseService->deleteWarehouse($id);

            return response()->json(['success' => true, 'message' => 'Warehouse Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Warehouse.', 'error' => $exception->getMessage()], 500);
        }
    }
}
