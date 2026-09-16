<?php

namespace App\Http\Controllers;

use App\DataTables\ProductUnitsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductUnit\StoreProductUnitRequest;
use App\Http\Requests\ProductUnit\UpdateProductUnitRequest;
use App\Models\ProductUnit;
use App\Services\ProductUnitService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductUnitsController extends Controller
{
    protected ProductUnitService $productUnitService;

    public function __construct(ProductUnitService $productUnitService)
    {
        $this->productUnitService = $productUnitService;
    }

    public function index(ProductUnitsDataTable $productUnitsDataTable)
    {
        $productUnitsDataTable->showTrashed = false;

        return $productUnitsDataTable->render('product_unit.index');
    }

    public function create()
    {
        return view('product_unit.create');
    }

    public function store(StoreProductUnitRequest $productUnitRequest)
    {
        try {
            $this->productUnitService->storeProductUnit($productUnitRequest->validated());

            return redirect()->route('product-unit.index')->with('success', 'New Product-Unit Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Product-Unit Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Product-Unit: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductUnit $productUnit)
    {
        $data['productUnit'] = $productUnit->load(['creator', 'updater', 'deleter']);

        return view('product_unit.show', $data);
    }

    public function edit(ProductUnit $productUnit): View
    {
        $data['productUnit'] = $productUnit;

        return view('product_unit.edit', $data);
    }

    public function update(UpdateProductUnitRequest $productUnitRequest, ProductUnit $productUnit): RedirectResponse
    {
        try {
            $this->productUnitService->updateProductUnit($productUnit, $productUnitRequest->validated());

            return redirect()->route('product-unit.index')->with('success', 'Product-Unit Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Product-Unit Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Product-Unit: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->productUnitService->destroyProductUnit($id);

            return response()->json(['success' => true, 'message' => 'Product-Unit Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Product-Unit Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Product-Unit Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Product-Unit Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Product-Unit: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Product-Unit.'], 500);
        }
    }

    public function trash(ProductUnitsDataTable $productUnitsDataTable)
    {
        $productUnitsDataTable->showTrashed = true;

        return $productUnitsDataTable->render('product_unit.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->productUnitService->restoreProductUnit($id);

            return response()->json(['success' => true, 'message' => 'Product-Unit Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Product-Unit Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Product-Unit Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Product-Unit Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Product-Unit: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Product-Unit.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->productUnitService->deleteProductUnit($id);

            return response()->json(['success' => true, 'message' => 'Product-Unit Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Product-Unit.', 'error' => $exception->getMessage()], 500);
        }
    }
}
