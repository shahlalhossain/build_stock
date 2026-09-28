<?php

namespace App\Http\Controllers;

use App\DataTables\ProductPurchasesDataTable;
use App\DataTables\RequisitionsAvailableForPurchaseDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductPurchase\StoreProductPurchaseRequest;
use App\Http\Requests\ProductPurchase\UpdateProductPurchaseRequest;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductRequisition;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\ProductPurchaseService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductPurchasesController extends Controller
{
    protected ProductPurchaseService $productPurchaseService;

    public function __construct(ProductPurchaseService $productPurchaseService)
    {
        $this->productPurchaseService = $productPurchaseService;
    }

    public function index(ProductPurchasesDataTable $productPurchasesDataTable)
    {
        $productPurchasesDataTable->showTrashed = false;

        return $productPurchasesDataTable->render('product-purchase.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product-purchase.create', $data);
    }

    public function store(StoreProductPurchaseRequest $productPurchaseRequest)
    {
        try {
            $this->productPurchaseService->storePurchase($productPurchaseRequest->validated());

            return redirect()->route('product-purchase.index')->with('success', 'New Purchase Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Purchase Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Purchase: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * List of Approved Requisitions still Eligible for Purchase (Full or Partial).
     */
    public function requisitionList(RequisitionsAvailableForPurchaseDataTable $requisitionsDataTable)
    {
        return $requisitionsDataTable->render('product-purchase.requisition-list');
    }

    /**
     * The Locked-Down Purchase Form for a single Requisition — only the
     * Requisition's own Products may be Purchased, Quantity capped at Remaining.
     */
    public function createFromRequisition(ProductRequisition $productRequisition)
    {
        try {
            $requisition = $this->productPurchaseService->getRequisitionForPurchase($productRequisition->id);
        } catch (GeneralException $generalException) {
            return redirect()->route('product-purchase.requisition-list')->with('error', $generalException->getMessage());
        }

        $data = $this->formLookups();
        $data['requisition'] = $requisition;

        return view('product-purchase.create-from-requisition', $data);
    }

    public function storeFromRequisition(StoreProductPurchaseRequest $productPurchaseRequest)
    {
        try {
            $this->productPurchaseService->storePurchase($productPurchaseRequest->validated());

            return redirect()->route('product-purchase.index')->with('success', 'New Purchase Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Purchase against Requisition Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Purchase against Requisition: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductPurchase $productPurchase)
    {
        $data['productPurchase'] = $productPurchase->load([
            'requisition',
            'store',
            'supplier',
            'items.product',
            'items.productVariant',
            'items.unit',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('product-purchase.show', $data);
    }

    public function edit(ProductPurchase $productPurchase): View
    {
        $data = $this->formLookups();
        $data['productPurchase'] = $productPurchase->load(['items.product', 'items.productVariant', 'items.unit']);

        return view('product-purchase.edit', $data);
    }

    public function update(UpdateProductPurchaseRequest $productPurchaseRequest, ProductPurchase $productPurchase): RedirectResponse
    {
        try {
            $this->productPurchaseService->updatePurchase($productPurchase, $productPurchaseRequest->validated());

            return redirect()->route('product-purchase.index')->with('success', 'Purchase Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Purchase Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Purchase: '.$exception->getMessage());

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
            $this->productPurchaseService->updatePurchaseStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Purchase Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Purchase Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Purchase Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Purchase Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Purchase Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Purchase Status.'], 500);
        }
    }

    public function downloadInvoiceAttachment(ProductPurchase $productPurchase): StreamedResponse
    {
        abort_unless($productPurchase->invoice_attachment_path, 404);
        abort_unless(Storage::disk('local')->exists($productPurchase->invoice_attachment_path), 404);

        return Storage::disk('local')->download($productPurchase->invoice_attachment_path);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->productPurchaseService->destroyPurchase($id);

            return response()->json(['success' => true, 'message' => 'Purchase Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Purchase Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Purchase Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Purchase Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Purchase: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Purchase.'], 500);
        }
    }

    public function trash(ProductPurchasesDataTable $productPurchasesDataTable)
    {
        $productPurchasesDataTable->showTrashed = true;

        return $productPurchasesDataTable->render('product-purchase.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->productPurchaseService->restorePurchase($id);

            return response()->json(['success' => true, 'message' => 'Purchase Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Purchase Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Purchase Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Purchase Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Purchase: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Purchase.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->productPurchaseService->deletePurchase($id);

            return response()->json(['success' => true, 'message' => 'Purchase Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Purchase.', 'error' => $exception->getMessage()], 500);
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
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'requisitions' => ProductRequisition::query()->where('status', ProductRequisition::STATUS_APPROVED)->orderBy('code')->get(['id', 'code']),
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
