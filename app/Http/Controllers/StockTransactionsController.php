<?php

namespace App\Http\Controllers;

use App\DataTables\StockTransactionsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\StockTransaction\StoreStockTransactionRequest;
use App\Http\Requests\StockTransaction\UpdateStockTransactionRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockTransactionService;
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

class StockTransactionsController extends Controller
{
    protected StockTransactionService $stockTransactionService;

    protected StoreAccessService $storeAccessService;

    public function __construct(StockTransactionService $stockTransactionService, StoreAccessService $storeAccessService)
    {
        $this->stockTransactionService = $stockTransactionService;
        $this->storeAccessService = $storeAccessService;
    }

    public function index(StockTransactionsDataTable $stockTransactionsDataTable)
    {
        $stockTransactionsDataTable->showTrashed = false;

        return $stockTransactionsDataTable->render('stock-transaction.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('stock-transaction.create', $data);
    }

    public function store(StoreStockTransactionRequest $stockTransactionRequest)
    {
        try {
            $this->stockTransactionService->storeTransaction($stockTransactionRequest->validated());

            return redirect()->route('stock-transaction.index')->with('success', 'New Stock Transaction Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Stock Transaction Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Stock Transaction: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(StockTransaction $stockTransaction)
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        $data['stockTransaction'] = $stockTransaction->load([
            'store',
            'items.product',
            'items.productVariant',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('stock-transaction.show', $data);
    }

    public function edit(StockTransaction $stockTransaction): View
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        $data = $this->formLookups();
        $data['stockTransaction'] = $stockTransaction->load(['items.product', 'items.productVariant']);

        return view('stock-transaction.edit', $data);
    }

    public function update(UpdateStockTransactionRequest $stockTransactionRequest, StockTransaction $stockTransaction): RedirectResponse
    {
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        try {
            $this->stockTransactionService->updateTransaction($stockTransaction, $stockTransactionRequest->validated());

            return redirect()->route('stock-transaction.index')->with('success', 'Stock Transaction Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Stock Transaction Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Stock Transaction: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $stockTransaction = StockTransaction::findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $this->stockTransactionService->updateTransactionStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Stock Transaction Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Stock Transaction Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Stock Transaction Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Stock Transaction Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Stock Transaction Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Stock Transaction Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $stockTransaction = StockTransaction::findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        try {
            $this->stockTransactionService->destroyTransaction($id);

            return response()->json(['success' => true, 'message' => 'Stock Transaction Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Stock Transaction Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Stock Transaction Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Stock Transaction Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Stock Transaction: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Stock Transaction.'], 500);
        }
    }

    public function trash(StockTransactionsDataTable $stockTransactionsDataTable)
    {
        $stockTransactionsDataTable->showTrashed = true;

        return $stockTransactionsDataTable->render('stock-transaction.trashed');
    }

    public function restore($id): JsonResponse
    {
        $stockTransaction = StockTransaction::withTrashed()->findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        try {
            $this->stockTransactionService->restoreTransaction($id);

            return response()->json(['success' => true, 'message' => 'Stock Transaction Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Stock Transaction Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Stock Transaction Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Stock Transaction Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Stock Transaction: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Stock Transaction.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        $stockTransaction = StockTransaction::withTrashed()->findOrFail($id);
        abort_unless($this->storeAccessService->canAccessStore(Auth::user(), $stockTransaction->store_id), 403);

        try {
            $this->stockTransactionService->deleteTransaction($id);

            return response()->json(['success' => true, 'message' => 'Stock Transaction Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Stock Transaction.', 'error' => $exception->getMessage()], 500);
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
            ->get(['id', 'name', 'code']);

        return [
            'stores' => $this->storeAccessService->selectableStores(Auth::user()),
            'defaultStoreId' => $this->storeAccessService->defaultStoreIdForCreate(Auth::user()),
            'products' => $products,
            // Preloaded per-Product Variant list as JSON (no AJAX round-trip), matching
            // this app's existing Category/Sub-Category and Specifications client-side
            // filter convention. The "Setup Product Variants" modal lists these existing
            // Variants directly — it no longer builds Attribute x Value Combinations
            // client-side, since each Variant is now a real product_variants row.
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
