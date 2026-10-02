<?php

namespace App\Http\Controllers;

use App\DataTables\ProductTransfersDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductTransfer\StoreProductTransferRequest;
use App\Http\Requests\ProductTransfer\UpdateProductTransferRequest;
use App\Models\Product;
use App\Models\ProductRequisition;
use App\Models\ProductTransfer;
use App\Models\ProductUnit;
use App\Services\ProductTransferService;
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

class ProductTransfersController extends Controller
{
    protected ProductTransferService $productTransferService;

    protected StoreAccessService $storeAccessService;

    public function __construct(ProductTransferService $productTransferService, StoreAccessService $storeAccessService)
    {
        $this->productTransferService = $productTransferService;
        $this->storeAccessService = $storeAccessService;
    }

    /**
     * A Transfer is Visible if EITHER its Source or Destination Store is in
     * the User's Visible Set — Staff on either End track the Movement, even
     * though only the Source-Side Attachment lets them Create one (see
     * formLookups()'s sourceStores vs destinationStores split).
     */
    protected function canAccessTransfer(ProductTransfer $productTransfer): bool
    {
        return $this->storeAccessService->canAccessStore(Auth::user(), $productTransfer->source_store_id)
            || $this->storeAccessService->canAccessStore(Auth::user(), $productTransfer->destination_store_id);
    }

    public function index(ProductTransfersDataTable $productTransfersDataTable)
    {
        $productTransfersDataTable->showTrashed = false;

        return $productTransfersDataTable->render('product-transfer.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product-transfer.create', $data);
    }

    public function store(StoreProductTransferRequest $productTransferRequest)
    {
        try {
            $this->productTransferService->storeTransfer($productTransferRequest->validated());

            return redirect()->route('product-transfer.index')->with('success', 'New Transfer Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Transfer Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Transfer: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductTransfer $productTransfer)
    {
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        $data['productTransfer'] = $productTransfer->load([
            'requisition',
            'sourceStore',
            'destinationStore',
            'items.product',
            'items.productVariant',
            'items.unit',
            'receives',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('product-transfer.show', $data);
    }

    public function edit(ProductTransfer $productTransfer): View
    {
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        $data = $this->formLookups();
        $data['productTransfer'] = $productTransfer->load(['items.product', 'items.productVariant', 'items.unit']);

        return view('product-transfer.edit', $data);
    }

    public function update(UpdateProductTransferRequest $productTransferRequest, ProductTransfer $productTransfer): RedirectResponse
    {
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        try {
            $this->productTransferService->updateTransfer($productTransfer, $productTransferRequest->validated());

            return redirect()->route('product-transfer.index')->with('success', 'Transfer Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Transfer Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Transfer: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $productTransfer = ProductTransfer::findOrFail($id);
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $this->productTransferService->updateTransferStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Transfer Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Transfer Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Transfer Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Transfer Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Transfer Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Transfer Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $productTransfer = ProductTransfer::findOrFail($id);
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        try {
            $this->productTransferService->destroyTransfer($id);

            return response()->json(['success' => true, 'message' => 'Transfer Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Transfer Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Transfer Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Transfer Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Transfer: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Transfer.'], 500);
        }
    }

    public function trash(ProductTransfersDataTable $productTransfersDataTable)
    {
        $productTransfersDataTable->showTrashed = true;

        return $productTransfersDataTable->render('product-transfer.trashed');
    }

    public function restore($id): JsonResponse
    {
        $productTransfer = ProductTransfer::withTrashed()->findOrFail($id);
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        try {
            $this->productTransferService->restoreTransfer($id);

            return response()->json(['success' => true, 'message' => 'Transfer Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Transfer Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Transfer Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Transfer Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Transfer: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Transfer.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        $productTransfer = ProductTransfer::withTrashed()->findOrFail($id);
        abort_unless($this->canAccessTransfer($productTransfer), 403);

        try {
            $this->productTransferService->deleteTransfer($id);

            return response()->json(['success' => true, 'message' => 'Transfer Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Transfer.', 'error' => $exception->getMessage()], 500);
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

        $requisitionsQuery = ProductRequisition::query()->where('status', ProductRequisition::STATUS_APPROVED)->orderBy('code');

        return [
            'sourceStores' => $this->storeAccessService->selectableStores(Auth::user()),
            'destinationStores' => $this->storeAccessService->allActiveStores(),
            'defaultStoreId' => $this->storeAccessService->defaultStoreIdForCreate(Auth::user()),
            'requisitions' => $this->storeAccessService->scopeQueryToVisibleStores($requisitionsQuery, Auth::user())->get(['id', 'code']),
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
