<?php

namespace App\Http\Controllers;

use App\DataTables\ProductsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\SubCategory;
use App\Services\ProductService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductsController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(ProductsDataTable $productsDataTable)
    {
        $productsDataTable->showTrashed = false;

        return $productsDataTable->render('product.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product.create', $data);
    }

    public function store(StoreProductRequest $productRequest)
    {
        try {
            $this->productService->storeProduct($productRequest->validated());

            return redirect()->route('product.index')->with('success', 'New Product Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Product Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Product: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Product $product)
    {
        $data['product'] = $product->load(['category', 'subCategory', 'brand', 'unit', 'attributeValues.attribute', 'variants.attributeValues.attribute', 'creator', 'updater', 'deleter', 'approvalLogs.actionedBy']);

        return view('product.show', $data);
    }

    public function edit(Product $product): View
    {
        $data = $this->formLookups();
        $data['product'] = $product->load(['attributeValues', 'variants.attributeValues.attribute']);

        return view('product.edit', $data);
    }

    public function update(UpdateProductRequest $productRequest, Product $product): RedirectResponse
    {
        try {
            $this->productService->updateProduct($product, $productRequest->validated());

            return redirect()->route('product.index')->with('success', 'Product Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Product Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Product: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $this->productService->updateProductStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Product Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Product Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Product Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Product Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Product Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Product Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->productService->destroyProduct($id);

            return response()->json(['success' => true, 'message' => 'Product Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Product Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Product Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Product Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Product: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Product.'], 500);
        }
    }

    public function trash(ProductsDataTable $productsDataTable)
    {
        $productsDataTable->showTrashed = true;

        return $productsDataTable->render('product.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->productService->restoreProduct($id);

            return response()->json(['success' => true, 'message' => 'Product Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Product Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Product Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Product Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Product: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Product.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return response()->json(['success' => true, 'message' => 'Product Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Product.', 'error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Shared lookup data for the Create/Edit forms.
     */
    protected function formLookups(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subCategories' => SubCategory::where('is_active', true)->orderBy('name')->get(['id', 'category_id', 'name']),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'units' => ProductUnit::where('is_active', true)->orderBy('group')->orderBy('name')->get(['id', 'group', 'name', 'symbol']),
            'attributes' => Attribute::where('is_active', true)->with(['values' => function ($query) {
                $query->orderBy('value');
            }])->orderBy('name')->get(),
        ];
    }
}
