<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Product;
use App\Models\ProductDelivery;
use App\Models\ProductStock;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductDeliveryService.
 */
class ProductDeliveryService extends BaseService
{
    protected UnitConversionService $unitConversionService;

    /**
     * ProductDeliveryService Constructor.
     */
    public function __construct(ProductDelivery $productDelivery, UnitConversionService $unitConversionService)
    {
        $this->model = $productDelivery;
        $this->unitConversionService = $unitConversionService;
    }

    /**
     * Generate the next Sequential Code (e.g. DEL-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'DEL';

        $lastNumber = ProductDelivery::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeDelivery(array $data = []): ProductDelivery
    {
        DB::beginTransaction();
        try {
            $productDelivery = $this->model::create([
                'code' => $this->generateCode(),
                'store_id' => $data['store_id'] ?? null,
                'delivered_to' => $data['delivered_to'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->createItems($productDelivery, $data['items'] ?? []);

            DB::commit();

            return $productDelivery;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Delivery.'));
        }
    }

    /**
     * Create the Line Items for a given Delivery. No Cost/Price concept — a
     * Delivery only ever carries Quantity + Unit per Product/Variant.
     */
    protected function createItems(ProductDelivery $productDelivery, array $items): void
    {
        foreach ($items as $item) {
            $productDelivery->items()->create([
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
    public function updateDelivery(ProductDelivery $productDelivery, array $data = []): ProductDelivery
    {
        DB::beginTransaction();

        try {
            if ($productDelivery->status !== ProductDelivery::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Deliveries can be Updated.'));
            }

            $productDelivery->update([
                'store_id' => $data['store_id'] ?? null,
                'delivered_to' => $data['delivered_to'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $productDelivery->items()->delete();
            $this->createItems($productDelivery, $data['items'] ?? []);

            DB::commit();

            return $productDelivery;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Delivery.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateDeliveryStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $productDelivery = ProductDelivery::findOrFail($id);

            if ($productDelivery->status === $status) {
                throw new GeneralException(__('Delivery is Already in this Status.'));
            }

            if ($status === ProductDelivery::STATUS_APPROVED) {
                $this->applyStockEffect($productDelivery);
            }

            $oldStatus = $productDelivery->status;
            $productDelivery->status = $status;
            $productDelivery->saveQuietly();

            ApprovalLog::create([
                'model_type' => ProductDelivery::class,
                'model_id' => $productDelivery->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($productDelivery)
                ->causedBy(Auth::user())
                ->useLog('product_delivery')
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
            Log::error('Delivery Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Delivery Status Update'));
        }
    }

    /**
     * Apply the Stock Effect: a Delivery approval ONLY subtracts Stock at its
     * own Store, normalized to the Product's base Unit via
     * UnitConversionService. Must run inside the caller's DB Transaction.
     *
     * @throws GeneralException
     */
    protected function applyStockEffect(ProductDelivery $productDelivery): void
    {
        foreach ($productDelivery->items as $item) {
            $product = $item->product ?: Product::findOrFail($item->product_id);

            $baseQuantity = $this->unitConversionService->toBaseUnit($product, $item->unit_id, (float) $item->quantity);

            $productStock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->where('store_id', $productDelivery->store_id)
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
                    'store_id' => $productDelivery->store_id,
                    'quantity' => 0,
                ]);
            }

            $newQuantity = (float) $productStock->quantity - $baseQuantity;

            if ($newQuantity < 0) {
                $label = $product->code ? ($product->code.' — '.$product->name) : $product->name;

                throw new GeneralException(__('Insufficient Stock for Product :label at this Store. Approval Cancelled.', ['label' => $label]));
            }

            $productStock->quantity = $newQuantity;
            $productStock->save();
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyDelivery($id): bool
    {
        DB::beginTransaction();

        try {
            $productDelivery = ProductDelivery::findOrFail((int) $id);

            if ($productDelivery->status === ProductDelivery::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Delivery cannot be Destroyed.'));
            }

            $productDelivery->is_active = false;
            $productDelivery->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($productDelivery) {
                $productDelivery->save();
            });

            $result = $productDelivery->delete();

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
            Log::error('Delivery Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Delivery.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreDelivery($id): bool
    {
        DB::beginTransaction();
        try {
            $productDelivery = ProductDelivery::withTrashed()->findOrFail($id);

            $productDelivery->is_active = true;
            $productDelivery->deleted_by = null;

            $productDelivery->saveQuietly();

            $result = $productDelivery->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Delivery Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Delivery.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteDelivery($id): bool
    {
        DB::beginTransaction();
        try {
            $productDelivery = ProductDelivery::withTrashed()->findOrFail($id);

            if ($productDelivery->status === ProductDelivery::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Delivery cannot be Deleted.'));
            }

            activity()->withoutLogs(function () use ($productDelivery) {
                $productDelivery->forceDelete();
            });

            activity()
                ->useLog('product_delivery')
                ->event('forceDeleted')
                ->performedOn($productDelivery)
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
            Log::error('Delivery Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Delivery.'));
        }
    }
}
