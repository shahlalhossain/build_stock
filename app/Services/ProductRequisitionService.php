<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\ProductRequisition;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductRequisitionService.
 */
class ProductRequisitionService extends BaseService
{
    /**
     * ProductRequisitionService Constructor.
     */
    public function __construct(ProductRequisition $productRequisition)
    {
        $this->model = $productRequisition;
    }

    /**
     * Generate the next Sequential Code (e.g. REQ-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'REQ';

        $lastNumber = ProductRequisition::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeRequisition(array $data = []): ProductRequisition
    {
        DB::beginTransaction();
        try {
            $productRequisition = $this->model::create([
                'code' => $this->generateCode(),
                'store_id' => $data['store_id'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->createItems($productRequisition, $data['items'] ?? []);

            DB::commit();

            return $productRequisition;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Product Requisition.'));
        }
    }

    /**
     * Create the Line Items for a given Requisition. No Cost/Price concept — a
     * Requisition only ever carries Quantity + Unit per Product/Variant.
     */
    protected function createItems(ProductRequisition $productRequisition, array $items): void
    {
        foreach ($items as $item) {
            $productRequisition->items()->create([
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
    public function updateRequisition(ProductRequisition $productRequisition, array $data = []): ProductRequisition
    {
        DB::beginTransaction();

        try {
            if ($productRequisition->status !== ProductRequisition::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Requisitions can be Updated.'));
            }

            $productRequisition->update([
                'store_id' => $data['store_id'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $productRequisition->items()->delete();
            $this->createItems($productRequisition, $data['items'] ?? []);

            DB::commit();

            return $productRequisition;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Requisition.'));
        }
    }

    /**
     * Approve/Reject a Requisition. This NEVER touches product_stocks — a
     * Requisition is purely a request document; Purchase/Transfer are what
     * actually move Stock.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateRequisitionStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $productRequisition = ProductRequisition::findOrFail($id);

            if ($productRequisition->status === $status) {
                throw new GeneralException(__('Requisition is Already in this Status.'));
            }

            $oldStatus = $productRequisition->status;

            $productRequisition->status = $status;
            $productRequisition->saveQuietly();

            ApprovalLog::create([
                'model_type' => ProductRequisition::class,
                'model_id' => $productRequisition->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($productRequisition)
                ->causedBy(Auth::user())
                ->useLog('product_requisition')
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
            Log::error('Requisition Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Requisition Status Update'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyRequisition($id): bool
    {
        DB::beginTransaction();

        try {
            $productRequisition = ProductRequisition::findOrFail((int) $id);

            if ($productRequisition->status === ProductRequisition::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Requisition cannot be Destroyed.'));
            }

            $productRequisition->is_active = false;
            $productRequisition->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($productRequisition) {
                $productRequisition->save();
            });

            $result = $productRequisition->delete();

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
            Log::error('Requisition Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Requisition.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreRequisition($id): bool
    {
        DB::beginTransaction();
        try {
            $productRequisition = ProductRequisition::withTrashed()->findOrFail($id);

            $productRequisition->is_active = true;
            $productRequisition->deleted_by = null;

            $productRequisition->saveQuietly();

            $result = $productRequisition->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Requisition Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Requisition.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteRequisition($id): bool
    {
        DB::beginTransaction();
        try {
            $productRequisition = ProductRequisition::withTrashed()->findOrFail($id);

            if ($productRequisition->status === ProductRequisition::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Requisition cannot be Deleted.'));
            }

            activity()->withoutLogs(function () use ($productRequisition) {
                $productRequisition->forceDelete();
            });

            activity()
                ->useLog('product_requisition')
                ->event('forceDeleted')
                ->performedOn($productRequisition)
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
            Log::error('Requisition Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Requisition.'));
        }
    }
}
