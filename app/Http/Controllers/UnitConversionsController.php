<?php

namespace App\Http\Controllers;

use App\DataTables\UnitConversionsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\UnitConversion\StoreUnitConversionRequest;
use App\Http\Requests\UnitConversion\UpdateUnitConversionRequest;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\UnitConversion;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class UnitConversionsController extends Controller
{
    protected UnitConversionService $unitConversionService;

    public function __construct(UnitConversionService $unitConversionService)
    {
        $this->unitConversionService = $unitConversionService;
    }

    public function index(UnitConversionsDataTable $unitConversionsDataTable)
    {
        $unitConversionsDataTable->showTrashed = false;

        return $unitConversionsDataTable->render('unit_conversion.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('unit_conversion.create', $data);
    }

    public function store(StoreUnitConversionRequest $unitConversionRequest)
    {
        try {
            $this->unitConversionService->storeUnitConversion($unitConversionRequest->validated());

            return redirect()->route('unit-conversion.index')->with('success', 'New Unit Conversion Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Unit Conversion Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Unit Conversion: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(UnitConversion $unitConversion)
    {
        $data['unitConversion'] = $unitConversion->load(['product.unit', 'unit', 'creator', 'updater', 'deleter']);

        return view('unit_conversion.show', $data);
    }

    public function edit(UnitConversion $unitConversion): View
    {
        $data = $this->formLookups();
        $data['unitConversion'] = $unitConversion;

        return view('unit_conversion.edit', $data);
    }

    public function update(UpdateUnitConversionRequest $unitConversionRequest, UnitConversion $unitConversion): RedirectResponse
    {
        try {
            $this->unitConversionService->updateUnitConversion($unitConversion, $unitConversionRequest->validated());

            return redirect()->route('unit-conversion.index')->with('success', 'Unit Conversion Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Unit Conversion Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Unit Conversion: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->unitConversionService->destroyUnitConversion($id);

            return response()->json(['success' => true, 'message' => 'Unit Conversion Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Unit Conversion Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unit Conversion Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Unit Conversion Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Unit Conversion: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Unit Conversion.'], 500);
        }
    }

    public function trash(UnitConversionsDataTable $unitConversionsDataTable)
    {
        $unitConversionsDataTable->showTrashed = true;

        return $unitConversionsDataTable->render('unit_conversion.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $unitConversion = UnitConversion::withTrashed()->findOrFail($id);
            $unitConversion->is_active = true;
            $unitConversion->deleted_by = null;
            $unitConversion->saveQuietly();
            $unitConversion->restore();

            return response()->json(['success' => true, 'message' => 'Unit Conversion Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Unit Conversion Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unit Conversion Not Found.'], 404);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Unit Conversion: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Unit Conversion.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $unitConversion = UnitConversion::withTrashed()->findOrFail((int) $id);
            $unitConversion->forceDelete();

            return response()->json(['success' => true, 'message' => 'Unit Conversion Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Unit Conversion.', 'error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Shared Dropdown Data for the Create/Edit Forms.
     */
    protected function formLookups(): array
    {
        return [
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit_id']),
            'units' => ProductUnit::where('is_active', true)->orderBy('group')->orderBy('name')->get(['id', 'group', 'name', 'symbol']),
        ];
    }
}
