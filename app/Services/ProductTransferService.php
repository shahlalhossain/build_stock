<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductTransfer;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductTransferService.
 */
class ProductTransferService extends BaseService
{
    protected UnitConversionService $unitConversionService;

    /**
     * ProductTransferService Constructor.
     */
    public function __construct(ProductTransfer $productTransfer, UnitConversionService $unitConversionService)
    {
        $this->model = $productTransfer;
        $this->unitConversionService = $unitConversionService;
    }

    /**
     * Generate the next Sequential Code (e.g. TRF-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'TRF';

        $lastNumber = ProductTransfer::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeTransfer(array $data = []): ProductTransfer
    {
        DB::beginTransaction();
        try {
            $sourceStoreId = $data['source_store_id'] ?? null;
            $destinationStoreId = $data['destination_store_id'] ?? null;

            if (! $destinationStoreId || (int) $destinationStoreId === (int) $sourceStoreId) {
                throw new GeneralException(__('Destination Store must be Different from the Source Store.'));
            }

            $productTransfer = $this->model::create([
                'code' => $this->generateCode(),
                'requisition_id' => $data['requisition_id'] ?? null,
                'source_store_id' => $sourceStoreId,
                'destination_store_id' => $destinationStoreId,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->createItems($productTransfer, $data['items'] ?? []);

            DB::commit();

            return $productTransfer;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Transfer.'));
        }
    }

    /**
     * Create the Line Items for a given Transfer. No Cost/Price concept — a
     * Transfer only ever carries Quantity + Unit per Product/Variant.
     */
    protected function createItems(ProductTransfer $productTransfer, array $items): void
    {
        foreach ($items as $item) {
            $productTransfer->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateTransfer(ProductTransfer $productTransfer, array $data = []): ProductTransfer
    {
        DB::beginTransaction();

        try {
            if ($productTransfer->status !== ProductTransfer::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Transfers can be Updated.'));
            }

            $sourceStoreId = $data['source_store_id'] ?? null;
            $destinationStoreId = $data['destination_store_id'] ?? null;

            if (! $destinationStoreId || (int) $destinationStoreId === (int) $sourceStoreId) {
                throw new GeneralException(__('Destination Store must be Different from the Source Store.'));
            }

            $productTransfer->update([
                'requisition_id' => $data['requisition_id'] ?? null,
                'source_store_id' => $sourceStoreId,
                'destination_store_id' => $destinationStoreId,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $productTransfer->items()->delete();
            $this->createItems($productTransfer, $data['items'] ?? []);

            DB::commit();

            return $productTransfer;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Transfer.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateTransferStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $productTransfer = ProductTransfer::findOrFail($id);

            if ($productTransfer->status === $status) {
                throw new GeneralException(__('Transfer is Already in this Status.'));
            }

            if ($status === ProductTransfer::STATUS_APPROVED) {
                $this->applyStockEffect($productTransfer);
            }

            $oldStatus = $productTransfer->status;
            $productTransfer->status = $status;
            $productTransfer->saveQuietly();

            ApprovalLog::create([
                'model_type' => ProductTransfer::class,
                'model_id' => $productTransfer->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($productTransfer)
                ->causedBy(Auth::user())
                ->useLog('product_transfer')
                ->event('statusUpdated')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'remarks' => $remarks,
                ])
                ->log('statusUpdated');

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Transfer Status Update'));
        }
    }

    /**
     * Apply the Stock Effect: a Transfer approval ONLY subtracts Stock at the
     * Source Store, normalized to the Product's base Unit via
     * UnitConversionService. It never adds at the Destination — that is
     * exclusively Receive's responsibility (see ProductReceiveService).
     * Must run inside the caller's DB Transaction.
     *
     * @throws GeneralException
     */
    protected function applyStockEffect(ProductTransfer $productTransfer): void
    {
        foreach ($productTransfer->items as $item) {
            $product = $item->product ?: Product::findOrFail($item->product_id);

            $baseQuantity = $this->unitConversionService->toBaseUnit($product, $item->unit_id, (float) $item->quantity);

            $productStock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->where('store_id', $productTransfer->source_store_id)
                ->when(
                    $item->product_variant_id !== null,
                    fn ($query) => $query->where('product_variant_id', $item->product_variant_id),
                    fn ($query) => $query->whereNull('product_variant_id')
                )
                ->lockForUpdate()
                ->first();

            if (! $productStock) {
                $productStock = ProductStock::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'store_id' => $productTransfer->source_store_id,
                    'quantity' => 0,
                ]);
            }

            $newQuantity = (float) $productStock->quantity - $baseQuantity;

            if ($newQuantity < 0) {
                $label = $product->code ? ($product->code.' — '.$product->name) : $product->name;

                throw new GeneralException(__('Insufficient Stock for Product :label at the Source Store. Approval Cancelled.', ['label' => $label]));
            }

            $productStock->quantity = $newQuantity;
            $productStock->save();
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyTransfer($id): bool
    {
        DB::beginTransaction();

        try {
            $productTransfer = ProductTransfer::findOrFail((int) $id);

            if ($productTransfer->status === ProductTransfer::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Transfer cannot be Destroyed.'));
            }

            $productTransfer->is_active = false;
            $productTransfer->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($productTransfer) {
                $productTransfer->save();
            });

            $result = $productTransfer->delete();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Transfer.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreTransfer($id): bool
    {
        DB::beginTransaction();
        try {
            $productTransfer = ProductTransfer::withTrashed()->findOrFail($id);

            $productTransfer->is_active = true;
            $productTransfer->deleted_by = null;

            $productTransfer->saveQuietly();

            $result = $productTransfer->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Transfer.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteTransfer($id): bool
    {
        DB::beginTransaction();
        try {
            $productTransfer = ProductTransfer::withTrashed()->findOrFail($id);

            if ($productTransfer->status === ProductTransfer::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Transfer cannot be Deleted.'));
            }

            activity()->withoutLogs(function () use ($productTransfer) {
                $productTransfer->forceDelete();
            });

            activity()
                ->useLog('product_transfer')
                ->event('forceDeleted')
                ->performedOn($productTransfer)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Transfer.'));
        }
    }
}
