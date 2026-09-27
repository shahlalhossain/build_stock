<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Product;
use App\Models\ProductReceive;
use App\Models\ProductStock;
use App\Models\ProductTransfer;
use App\Models\ProductTransferItem;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductReceiveService.
 */
class ProductReceiveService extends BaseService
{
    protected UnitConversionService $unitConversionService;

    /**
     * ProductReceiveService Constructor.
     */
    public function __construct(ProductReceive $productReceive, UnitConversionService $unitConversionService)
    {
        $this->model = $productReceive;
        $this->unitConversionService = $unitConversionService;
    }

    /**
     * Generate the next Sequential Code (e.g. RCV-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'RCV';

        $lastNumber = ProductReceive::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The Remaining Quantity still owed against a Transfer Line, in that Line's
     * OWN Unit — the Transfer Line's sent Quantity minus every APPROVED Receive
     * Item's received_quantity normalized back into the Transfer Line's Unit.
     *
     * Rejected/Pending Receives do not count against the remaining balance —
     * only an Approved Receive actually confirms goods arrived.
     *
     * @throws GeneralException
     */
    public function remainingQuantity(ProductTransferItem $transferItem): float
    {
        $product = $transferItem->product ?: Product::findOrFail($transferItem->product_id);

        $receivedInTransferUnit = 0.0;

        foreach ($transferItem->receiveItems as $receiveItem) {
            if ($receiveItem->productReceive?->status !== ProductReceive::STATUS_APPROVED) {
                continue;
            }

            // Normalize both the Transfer Line's Unit and the Receive Item's own
            // Unit into the Product's base Unit, then compare/accumulate in that
            // common base Unit before converting the total back to the Transfer
            // Line's Unit for an apples-to-apples "remaining" figure.
            $receivedInBaseUnit = $this->unitConversionService->toBaseUnit(
                $product,
                $receiveItem->unit_id,
                (float) $receiveItem->received_quantity
            );

            $receivedInTransferUnit += $this->fromBaseUnit($product, $transferItem->unit_id, $receivedInBaseUnit);
        }

        return max(0.0, (float) $transferItem->quantity - $receivedInTransferUnit);
    }

    /**
     * Inverse of UnitConversionService::toBaseUnit — convert a base-Unit Quantity
     * back into an arbitrary Unit, for display/"remaining" purposes only. Never
     * used to compute an actual Stock Effect (that always normalizes TO base).
     *
     * @throws GeneralException
     */
    protected function fromBaseUnit(Product $product, ?int $unitId, float $baseQuantity): float
    {
        if (! $unitId || (int) $unitId === (int) $product->unit_id) {
            return $baseQuantity;
        }

        $oneUnitInBase = $this->unitConversionService->toBaseUnit($product, $unitId, 1.0);

        if ($oneUnitInBase <= 0) {
            throw new GeneralException(__('Invalid Unit Conversion Factor Configured for :product.', ['product' => $product->name]));
        }

        return $baseQuantity / $oneUnitInBase;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeReceive(array $data = []): ProductReceive
    {
        DB::beginTransaction();
        try {
            $transferId = $data['transfer_id'] ?? null;
            $transfer = ProductTransfer::findOrFail($transferId);

            if ($transfer->status !== ProductTransfer::STATUS_APPROVED) {
                throw new GeneralException(__('Only an Approved Transfer can be Received Against.'));
            }

            $productReceive = $this->model::create([
                'code' => $this->generateCode(),
                'transfer_id' => $transferId,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->createItems($productReceive, $data['items'] ?? []);

            DB::commit();

            return $productReceive;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Receive.'));
        }
    }

    /**
     * Create the Line Items for a given Receive, guarding that the Received
     * Quantity per Transfer Line never exceeds what is still Remaining.
     *
     * @throws GeneralException
     */
    protected function createItems(ProductReceive $productReceive, array $items): void
    {
        foreach ($items as $item) {
            $transferItemId = $item['transfer_item_id'] ?? null;
            $transferItem = ProductTransferItem::findOrFail($transferItemId);

            $receivedQuantity = (float) ($item['received_quantity'] ?? 0);

            if ($receivedQuantity <= 0) {
                continue;
            }

            $remaining = $this->remainingQuantity($transferItem);

            if ($receivedQuantity - $remaining > 0.01) {
                throw new GeneralException(__(
                    'Received Quantity (:received) for :product Exceeds the Remaining Transfer Quantity (:remaining).',
                    ['received' => $receivedQuantity, 'product' => $transferItem->product?->name, 'remaining' => $remaining]
                ));
            }

            $productReceive->items()->create([
                'transfer_item_id' => $transferItemId,
                'product_id' => $transferItem->product_id,
                'product_variant_id' => $transferItem->product_variant_id,
                'unit_id' => $item['unit_id'] ?? $transferItem->unit_id,
                'received_quantity' => $receivedQuantity,
                'variance_remarks' => $item['variance_remarks'] ?? null,
            ]);
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateReceive(ProductReceive $productReceive, array $data = []): ProductReceive
    {
        DB::beginTransaction();

        try {
            if ($productReceive->status !== ProductReceive::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Receives can be Updated.'));
            }

            $productReceive->update([
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $productReceive->items()->delete();
            $this->createItems($productReceive, $data['items'] ?? []);

            DB::commit();

            return $productReceive;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Receive.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateReceiveStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $productReceive = ProductReceive::findOrFail($id);

            if ($productReceive->status === $status) {
                throw new GeneralException(__('Receive is Already in this Status.'));
            }

            if ($status === ProductReceive::STATUS_APPROVED) {
                $this->applyStockEffect($productReceive);
            }

            $oldStatus = $productReceive->status;
            $productReceive->status = $status;
            $productReceive->saveQuietly();

            ApprovalLog::create([
                'model_type' => ProductReceive::class,
                'model_id' => $productReceive->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($productReceive)
                ->causedBy(Auth::user())
                ->useLog('product_receive')
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
            Log::error('Receive Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Receive Status Update'));
        }
    }

    /**
     * Apply the Stock Effect: a Receive approval ONLY adds Stock at the
     * Transfer's Destination Store, normalized to the Product's base Unit via
     * UnitConversionService. Must run inside the caller's DB Transaction.
     *
     * @throws GeneralException
     */
    protected function applyStockEffect(ProductReceive $productReceive): void
    {
        $destinationStoreId = $productReceive->transfer->destination_store_id;

        foreach ($productReceive->items as $item) {
            $product = $item->product ?: Product::findOrFail($item->product_id);

            $baseQuantity = $this->unitConversionService->toBaseUnit($product, $item->unit_id, (float) $item->received_quantity);

            $productStock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->where('store_id', $destinationStoreId)
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
                    'store_id' => $destinationStoreId,
                    'quantity' => 0,
                ]);
            }

            $productStock->quantity = (float) $productStock->quantity + $baseQuantity;
            $productStock->save();
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyReceive($id): bool
    {
        DB::beginTransaction();

        try {
            $productReceive = ProductReceive::findOrFail((int) $id);

            if ($productReceive->status === ProductReceive::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Receive cannot be Destroyed.'));
            }

            $productReceive->is_active = false;
            $productReceive->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($productReceive) {
                $productReceive->save();
            });

            $result = $productReceive->delete();

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
            Log::error('Receive Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Receive.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreReceive($id): bool
    {
        DB::beginTransaction();
        try {
            $productReceive = ProductReceive::withTrashed()->findOrFail($id);

            $productReceive->is_active = true;
            $productReceive->deleted_by = null;

            $productReceive->saveQuietly();

            $result = $productReceive->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Receive Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Receive.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteReceive($id): bool
    {
        DB::beginTransaction();
        try {
            $productReceive = ProductReceive::withTrashed()->findOrFail($id);

            if ($productReceive->status === ProductReceive::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Receive cannot be Deleted.'));
            }

            activity()->withoutLogs(function () use ($productReceive) {
                $productReceive->forceDelete();
            });

            activity()
                ->useLog('product_receive')
                ->event('forceDeleted')
                ->performedOn($productReceive)
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
            Log::error('Receive Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Receive.'));
        }
    }
}
