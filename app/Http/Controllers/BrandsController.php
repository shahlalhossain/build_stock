<?php

namespace App\Http\Controllers;

use App\DataTables\BrandsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Throwable;

class BrandsController extends Controller
{
    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(BrandsDataTable $brandsDataTable)
    {
        $brandsDataTable->showTrashed = false;
        return $brandsDataTable->render('brand.index');
    }

    public function create()
    {
        return view('brand.create');
    }

    public function store(StoreBrandRequest $brandRequest)
    {
        try {
            $this->brandService->storeBrand($brandRequest->validated());
            return redirect()->route('brand.index')->with('success', 'New Brand Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Brand Creation Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Brand: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Brand $brand)
    {
        $data['brand'] = $brand->load(['creator', 'updater', 'deleter']);
        return view('brand.show', $data);
    }

    public function edit(Brand $brand) : View
    {
        $data['brand'] = $brand;
        return view('brand.edit', $data);
    }

    public function update(UpdateBrandRequest $brandRequest, Brand $brand) : RedirectResponse
    {
        try {
            $this->brandService->updateBrand($brand, $brandRequest->validated());
            return redirect()->route('brand.index')->with('success', 'Brand Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Brand Update Failed: ' . $generalException->getMessage());
            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Brand: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * @throws Throwable
     * @throws GeneralException
     */
    public function updateStatus(Request $request, $id) : JsonResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:pending,approved,rejected'], 'remarks' => ['nullable', 'string']]);
        $this->brandService->updateBrandStatus((int)$id, $validated['status'], $validated['remarks'] ?? null );
        return response()->json(['success' => true, 'message' => __('Brand Status Updated Successfully.')]);
    }

    public function destroy($id) : JsonResponse
    {
        try {
            $this->brandService->destroyBrand($id);
            return response()->json(['success' => true, 'message' => 'Brand Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Brand Not Found' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Brand Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Brand Deletion Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Brand: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Brand.'], 500);
        }
    }

    public function trash(BrandsDataTable $brandsDataTable)
    {
        $brandsDataTable->showTrashed = true;
        return $brandsDataTable->render('brand.trashed');
    }

    public function restore($id) : JsonResponse
    {
        try {
            $this->brandService->restoreBrand($id);
            return response()->json(['success' => true, 'message' => 'Brand Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Brand Not Found: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Brand Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Brand Restoration Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Brand: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Brand.'], 500);
        }
    }

    public function delete($id) : JsonResponse
    {
        try {
            $this->brandService->deleteBrand($id);
            return response()->json(['success' => true, 'message' => 'Brand Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete Brand.', 'error' => $exception->getMessage()], 500);
        }
    }
}
