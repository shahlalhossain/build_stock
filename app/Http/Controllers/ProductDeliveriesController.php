<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDeliveriesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductDelivery\StoreProductDeliveryRequest;
use App\Http\Requests\ProductDelivery\UpdateProductDeliveryRequest;
use App\Models\Product;
use App\Models\ProductDelivery;
use App\Models\ProductUnit;
use App\Services\ProductDeliveryService;
use App\Services\StoreAccessService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductDeliveriesController extends Controller
{
    protected ProductDeliveryService $productDeliveryService;

    protected StoreAccessService $storeAccessService;

    public function __construct(ProductDeliveryService $productDeliveryService, StoreAccessService $storeAccessService)
    {
        $this->productDeliveryService = $productDeliveryService;
        $this->storeAccessService = $storeAccessService;
    }

    public function index(ProductDeliveriesDataTable $productDeliveriesDataTable)
    {
        $productDeliveriesDataTable->showTrashed = false;

        return $productDeliveriesDataTable->render('product-delivery.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product-delivery.create', $data);
    }

    public function store(StoreProductDeliveryRequest $productDeliveryRequest)
    {
        try {
            $this->productDeliveryService->storeDelivery($productDeliveryRequest->validated());

            return redirect()->route('product-delivery.index')->with('success', 'New Delivery Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Delivery Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Delivery: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductDelivery $productDelivery)
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        $data['productDelivery'] = $productDelivery->load([
            'store',
            'items.product',
            'items.productVariant',
            'items.unit',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('product-delivery.show', $data);
    }

    public function edit(ProductDelivery $productDelivery): View
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        $data = $this->formLookups();
        $data['productDelivery'] = $productDelivery->load(['items.product', 'items.productVariant', 'items.unit']);

        return view('product-delivery.edit', $data);
    }

    public function update(UpdateProductDeliveryRequest $productDeliveryRequest, ProductDelivery $productDelivery): RedirectResponse
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        try {
            $this->productDeliveryService->updateDelivery($productDelivery, $productDeliveryRequest->validated());

            return redirect()->route('product-delivery.index')->with('success', 'Delivery Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Delivery Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Delivery: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $productDelivery = ProductDelivery::findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $this->productDeliveryService->updateDeliveryStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Delivery Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Delivery Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Delivery Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Delivery Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Delivery Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Delivery Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $productDelivery = ProductDelivery::findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        try {
            $this->productDeliveryService->destroyDelivery($id);

            return response()->json(['success' => true, 'message' => 'Delivery Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Delivery Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Delivery Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Delivery Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Delivery: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Delivery.'], 500);
        }
    }

    public function trash(ProductDeliveriesDataTable $productDeliveriesDataTable)
    {
        $productDeliveriesDataTable->showTrashed = true;

        return $productDeliveriesDataTable->render('product-delivery.trashed');
    }

    public function restore($id): JsonResponse
    {
        $productDelivery = ProductDelivery::withTrashed()->findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        try {
            $this->productDeliveryService->restoreDelivery($id);

            return response()->json(['success' => true, 'message' => 'Delivery Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Delivery Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Delivery Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Delivery Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Delivery: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Delivery.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        $productDelivery = ProductDelivery::withTrashed()->findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $productDelivery->store_id), 403);

        try {
            $this->productDeliveryService->deleteDelivery($id);

            return response()->json(['success' => true, 'message' => 'Delivery Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Delivery.', 'error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Shared Dropdown Data for the Create/Edit Forms.
     */
    protected function formLookups(): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->with(['variants' => function ($query) {
                $query->where('is_active', true)->with('attributeValues.attribute');
            }])
            ->get(['id', 'name', 'code', 'unit_id']);

        return [
            'stores' => $this->storeAccessService->selectableStores(Auth::user()),
            'defaultStoreId' => $this->storeAccessService->defaultStoreIdForCreate(Auth::user()),
            'products' => $products,
            'units' => ProductUnit::where('is_active', true)->orderBy('group')->orderBy('name')->get(['id', 'group', 'name', 'symbol']),
            'productVariants' => $products->mapWithKeys(function (Product $product) {
                $variants = $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'label' => $variant->variant_name ?: $variant->attributeValues->pluck('value')->implode(' / '),
                    'sku' => $variant->sku,
                ])->values();

                return [$product->id => $variants];
            }),
        ];
    }
}
