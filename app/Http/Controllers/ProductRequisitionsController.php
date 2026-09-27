<?php

namespace App\Http\Controllers;

use App\DataTables\ProductRequisitionsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductRequisition\StoreProductRequisitionRequest;
use App\Http\Requests\ProductRequisition\UpdateProductRequisitionRequest;
use App\Models\Product;
use App\Models\ProductRequisition;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Services\ProductRequisitionService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductRequisitionsController extends Controller
{
    protected ProductRequisitionService $productRequisitionService;

    public function __construct(ProductRequisitionService $productRequisitionService)
    {
        $this->productRequisitionService = $productRequisitionService;
    }

    public function index(ProductRequisitionsDataTable $productRequisitionsDataTable)
    {
        $productRequisitionsDataTable->showTrashed = false;

        return $productRequisitionsDataTable->render('product-requisition.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product-requisition.create', $data);
    }

    public function store(StoreProductRequisitionRequest $productRequisitionRequest)
    {
        try {
            $this->productRequisitionService->storeRequisition($productRequisitionRequest->validated());

            return redirect()->route('product-requisition.index')->with('success', 'New Requisition Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Requisition Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Requisition: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductRequisition $productRequisition)
    {
        $data['productRequisition'] = $productRequisition->load([
            'store',
            'items.product',
            'items.productVariant',
            'items.unit',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('product-requisition.show', $data);
    }

    public function edit(ProductRequisition $productRequisition): View
    {
        $data = $this->formLookups();
        $data['productRequisition'] = $productRequisition->load(['items.product', 'items.productVariant', 'items.unit']);

        return view('product-requisition.edit', $data);
    }

    public function update(UpdateProductRequisitionRequest $productRequisitionRequest, ProductRequisition $productRequisition): RedirectResponse
    {
        try {
            $this->productRequisitionService->updateRequisition($productRequisition, $productRequisitionRequest->validated());

            return redirect()->route('product-requisition.index')->with('success', 'Requisition Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Requisition Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Requisition: '.$exception->getMessage());

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
            $this->productRequisitionService->updateRequisitionStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Requisition Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Requisition Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Requisition Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Requisition Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Requisition Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Requisition Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->productRequisitionService->destroyRequisition($id);

            return response()->json(['success' => true, 'message' => 'Requisition Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Requisition Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Requisition Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Requisition Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Requisition: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Requisition.'], 500);
        }
    }

    public function trash(ProductRequisitionsDataTable $productRequisitionsDataTable)
    {
        $productRequisitionsDataTable->showTrashed = true;

        return $productRequisitionsDataTable->render('product-requisition.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->productRequisitionService->restoreRequisition($id);

            return response()->json(['success' => true, 'message' => 'Requisition Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Requisition Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Requisition Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Requisition Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Requisition: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Requisition.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->productRequisitionService->deleteRequisition($id);

            return response()->json(['success' => true, 'message' => 'Requisition Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Requisition.', 'error' => $exception->getMessage()], 500);
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
            'stores' => Store::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
