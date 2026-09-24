<?php

namespace App\Http\Controllers;

use App\DataTables\StockTransactionsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\StockTransaction\StoreStockTransactionRequest;
use App\Http\Requests\StockTransaction\UpdateStockTransactionRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\StockTransactionService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class StockTransactionsController extends Controller
{
    protected StockTransactionService $stockTransactionService;

    public function __construct(StockTransactionService $stockTransactionService)
    {
        $this->stockTransactionService = $stockTransactionService;
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
        $data['stockTransaction'] = $stockTransaction->load([
            'store',
            'supplier',
            'linkedTransaction',
            'items.product',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('stock-transaction.show', $data);
    }

    public function edit(StockTransaction $stockTransaction): View
    {
        $data = $this->formLookups();
        $data['stockTransaction'] = $stockTransaction->load(['items.product', 'linkedTransaction']);

        return view('stock-transaction.edit', $data);
    }

    public function update(UpdateStockTransactionRequest $stockTransactionRequest, StockTransaction $stockTransaction): RedirectResponse
    {
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
            ->with(['productAttributeValues.attribute', 'productAttributeValues.attributeValue'])
            ->get(['id', 'name', 'code']);

        return [
            'stores' => Store::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => $products,
            // Preloaded per-Product Variant Attribute-Values as JSON (no AJAX round-trip),
            // matching this app's existing Category/Sub-Category and Specifications
            // client-side filter convention. Grouped by Attribute so the "Setup Product
            // Variants" modal can build the Attribute x Value Combinations client-side.
            'productVariantAttributes' => $products->mapWithKeys(function (Product $product) {
                $groups = $product->productAttributeValues
                    ->groupBy('attribute_id')
                    ->map(function ($rows) {
                        $attribute = $rows->first()->attribute;

                        return [
                            'attribute_id' => $attribute->id,
                            'attribute_name' => $attribute->name,
                            'values' => $rows->map(fn ($row) => [
                                'attribute_value_id' => $row->attribute_value_id,
                                'value' => $row->attributeValue->value,
                            ])->values(),
                        ];
                    })
                    ->values();

                return [$product->id => $groups];
            }),
        ];
    }
}
