<?php

namespace App\Http\Controllers;

use App\DataTables\ProductReceivesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\ProductReceive\StoreProductReceiveRequest;
use App\Http\Requests\ProductReceive\UpdateProductReceiveRequest;
use App\Models\ProductReceive;
use App\Models\ProductTransfer;
use App\Models\ProductUnit;
use App\Services\ProductReceiveService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProductReceivesController extends Controller
{
    protected ProductReceiveService $productReceiveService;

    public function __construct(ProductReceiveService $productReceiveService)
    {
        $this->productReceiveService = $productReceiveService;
    }

    public function index(ProductReceivesDataTable $productReceivesDataTable)
    {
        $productReceivesDataTable->showTrashed = false;

        return $productReceivesDataTable->render('product-receive.index');
    }

    public function create()
    {
        $data = $this->formLookups();

        return view('product-receive.create', $data);
    }

    public function store(StoreProductReceiveRequest $productReceiveRequest)
    {
        try {
            $this->productReceiveService->storeReceive($productReceiveRequest->validated());

            return redirect()->route('product-receive.index')->with('success', 'New Receive Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Receive Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Receive: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(ProductReceive $productReceive)
    {
        $data['productReceive'] = $productReceive->load([
            'transfer.sourceStore',
            'transfer.destinationStore',
            'items.product',
            'items.productVariant',
            'items.unit',
            'items.transferItem',
            'creator',
            'updater',
            'deleter',
            'approvalLogs.actionedBy',
        ]);

        return view('product-receive.show', $data);
    }

    public function edit(ProductReceive $productReceive): View
    {
        $data = $this->formLookups();
        $data['productReceive'] = $productReceive->load(['items.product', 'items.productVariant', 'items.unit', 'transfer.items']);

        return view('product-receive.edit', $data);
    }

    public function update(UpdateProductReceiveRequest $productReceiveRequest, ProductReceive $productReceive): RedirectResponse
    {
        try {
            $this->productReceiveService->updateReceive($productReceive, $productReceiveRequest->validated());

            return redirect()->route('product-receive.index')->with('success', 'Receive Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Receive Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Receive: '.$exception->getMessage());

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
            $this->productReceiveService->updateReceiveStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Receive Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Receive Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Receive Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Receive Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Receive Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Receive Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->productReceiveService->destroyReceive($id);

            return response()->json(['success' => true, 'message' => 'Receive Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Receive Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Receive Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Receive Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Receive: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Receive.'], 500);
        }
    }

    public function trash(ProductReceivesDataTable $productReceivesDataTable)
    {
        $productReceivesDataTable->showTrashed = true;

        return $productReceivesDataTable->render('product-receive.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->productReceiveService->restoreReceive($id);

            return response()->json(['success' => true, 'message' => 'Receive Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Receive Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Receive Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Receive Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Receive: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Receive.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->productReceiveService->deleteReceive($id);

            return response()->json(['success' => true, 'message' => 'Receive Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Receive.', 'error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Shared Dropdown Data for the Create/Edit Forms — every Approved Transfer
     * with at least one Line still owing a Remaining Quantity, each Line
     * pre-computed with its Remaining Quantity so the page can auto-populate
     * Receive Rows without a further round-trip.
     */
    protected function formLookups(): array
    {
        $transfers = ProductTransfer::query()
            ->where('status', ProductTransfer::STATUS_APPROVED)
            ->with(['items.product', 'items.productVariant', 'items.unit', 'sourceStore', 'destinationStore'])
            ->orderBy('code')
            ->get();

        $transfersData = $transfers->map(function (ProductTransfer $transfer) {
            $lines = $transfer->items->map(function ($item) {
                $remaining = $this->productReceiveService->remainingQuantity($item);

                return [
                    'transfer_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_label' => $item->product?->name.($item->product?->code ? ' ('.$item->product->code.')' : ''),
                    'variant_label' => $item->productVariant?->variant_name ?? '',
                    'sent_quantity' => (float) $item->quantity,
                    'unit_id' => $item->unit_id,
                    'unit_label' => $item->unit?->name.($item->unit?->symbol ? ' ('.$item->unit->symbol.')' : ''),
                    'remaining_quantity' => round($remaining, 2),
                ];
            })->filter(fn ($line) => $line['remaining_quantity'] > 0.009)->values();

            return [
                'id' => $transfer->id,
                'code' => $transfer->code,
                'destination_store' => $transfer->destinationStore?->name,
                'lines' => $lines,
            ];
        })->filter(fn ($transfer) => $transfer['lines']->isNotEmpty())->values();

        return [
            'transfersData' => $transfersData,
            'units' => ProductUnit::where('is_active', true)->orderBy('group')->orderBy('name')->get(['id', 'group', 'name', 'symbol']),
        ];
    }
}
