<?php

namespace App\Services;

use App\Events\StockTransaction\StockTransactionCreated;
use App\Events\StockTransaction\StockTransactionDeleted;
use App\Events\StockTransaction\StockTransactionDestroyed;
use App\Events\StockTransaction\StockTransactionRestored;
use App\Events\StockTransaction\StockTransactionStatusUpdated;
use App\Events\StockTransaction\StockTransactionUpdated;
use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class StockTransactionService.
 */
class StockTransactionService extends BaseService
{
    /**
     * StockTransactionService Constructor.
     */
    public function __construct(StockTransaction $stockTransaction)
    {
        $this->model = $stockTransaction;
    }

    /**
     * Generate the next Sequential Code (e.g. STK-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'STK';

        $lastNumber = StockTransaction::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeTransaction(array $data = []): StockTransaction
    {
        $uploadedAttachmentPath = null;

        DB::beginTransaction();
        try {
            $type = $data['type'] ?? null;
            $items = $data['items'] ?? [];

            $headerProductFields = $this->headerProductFields($items);
            $purchaseFields = [];

            if ($type === StockTransaction::TYPE_PURCHASE) {
                $uploadedAttachmentPath = $this->storeInvoiceAttachment($data['invoice_attachment'] ?? null);
                $purchaseFields = $this->purchaseFields($data, $items, $uploadedAttachmentPath);
            }

            if ($type === StockTransaction::TYPE_TRANSFER) {
                $destinationStoreId = $data['destination_store_id'] ?? null;

                if (! $destinationStoreId || (int) $destinationStoreId === (int) ($data['store_id'] ?? null)) {
                    throw new GeneralException(__('Destination Store must be Different from the Source Store.'));
                }

                $transferOut = $this->model::create(array_merge($headerProductFields, [
                    'code' => $this->generateCode(),
                    'type' => StockTransaction::TYPE_TRANSFER_OUT,
                    'store_id' => $data['store_id'] ?? null,
                    'supplier_id' => null,
                    'linked_transaction_id' => null,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]));

                $transferIn = $this->model::create(array_merge($headerProductFields, [
                    'code' => $this->generateCode(),
                    'type' => StockTransaction::TYPE_TRANSFER_IN,
                    'store_id' => $destinationStoreId,
                    'supplier_id' => null,
                    'linked_transaction_id' => $transferOut->id,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]));

                $transferOut->update(['linked_transaction_id' => $transferIn->id]);

                $this->createItems($transferOut, $items);
                $this->createItems($transferIn, $items);

                event(new StockTransactionCreated($transferOut));
                event(new StockTransactionCreated($transferIn));

                DB::commit();

                return $transferOut;
            }

            $stockTransaction = $this->model::create(array_merge($headerProductFields, $purchaseFields, [
                'code' => $this->generateCode(),
                'type' => $type,
                'store_id' => $data['store_id'] ?? null,
                'supplier_id' => $type === StockTransaction::TYPE_PURCHASE ? ($data['supplier_id'] ?? null) : null,
                'linked_transaction_id' => null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]));

            $this->createItems($stockTransaction, $items);

            event(new StockTransactionCreated($stockTransaction));

            DB::commit();

            return $stockTransaction;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw new GeneralException(__('There was a Problem on Creating New Stock Transaction.'));
        }
    }

    /**
     * Store the Optional Invoice Attachment on the private "local" Disk.
     * Runs inside the caller's DB Transaction but writes to disk immediately — the
     * caller must clean this up via deleteInvoiceAttachment() on any Rollback.
     */
    protected function storeInvoiceAttachment(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('invoices/stock-transactions', 'local');
    }

    protected function deleteInvoiceAttachment(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Compute the Purchase-only Header Fields: per-Item Line Totals feed
     * total_amount, Discount/Tax then derive net_amount.
     */
    protected function purchaseFields(array $data, array $items, ?string $uploadedAttachmentPath): array
    {
        $totalAmount = $this->calculateItemsTotal($items);

        $discountType = $data['discount_type'] ?? null;
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $taxAmount = (float) ($data['tax_amount'] ?? 0);

        $discountValue = $discountType === StockTransaction::DISCOUNT_TYPE_PERCENTAGE
            ? $totalAmount * ($discountAmount / 100)
            : $discountAmount;

        $netAmount = $totalAmount - $discountValue + $taxAmount;

        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,
            'invoice_attachment_path' => $uploadedAttachmentPath,
            'discount_type' => $discountType,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'net_amount' => $netAmount,
            'payment_status' => $data['payment_status'] ?? StockTransaction::PAYMENT_STATUS_UNPAID,
            'paid_amount' => (float) ($data['paid_amount'] ?? 0),
        ];
    }

    /**
     * Sum every Line Item's Total (Quantity × Unit Cost) — Variant Rows are summed
     * individually, matching how createItems() explodes them into separate rows.
     */
    protected function calculateItemsTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $variants = $item['variants'] ?? [];

            if (empty($variants)) {
                $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_cost'] ?? 0);

                continue;
            }

            foreach ($variants as $variant) {
                $unitCost = $variant['unit_cost'] ?? ($item['unit_cost'] ?? 0);
                $total += (float) ($variant['quantity'] ?? 0) * (float) $unitCost;
            }
        }

        return $total;
    }

    /**
     * Derive the header-level product_id/product_variant_id from the first Line
     * Item (and its first selected Variant, if any) — a "primary item" convenience
     * reference only; stock_transaction_items remains the source of truth for
     * quantities. A Transaction with no Items yields nulls (both columns are nullable).
     */
    protected function headerProductFields(array $items): array
    {
        $firstItem = $items[0] ?? null;

        if (! $firstItem) {
            return ['product_id' => null, 'product_variant_id' => null];
        }

        $firstVariant = $firstItem['variants'][0] ?? null;

        return [
            'product_id' => $firstItem['product_id'] ?? null,
            'product_variant_id' => $firstVariant['product_variant_id'] ?? null,
        ];
    }

    /**
     * Create the Line Items for a given Stock Transaction.
     *
     * When an Item carries a Variant breakdown (from the "Setup Product Variants"
     * modal), one stock_transaction_item row is inserted per selected Variant, with
     * that Variant's own (whole, un-split) Quantity — each Variant is a real
     * product_variants row, so no accounting-split across Attribute Values is needed.
     *
     * @throws GeneralException
     */
    protected function createItems(StockTransaction $stockTransaction, array $items): void
    {
        foreach ($items as $item) {
            $variants = $item['variants'] ?? [];

            if (empty($variants)) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitCost = $item['unit_cost'] ?? null;

                $stockTransaction->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => null,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $unitCost !== null ? $quantity * (float) $unitCost : null,
                    'remarks' => $item['remarks'] ?? null,
                ]);

                continue;
            }

            foreach ($variants as $variant) {
                $quantity = (float) ($variant['quantity'] ?? 0);
                $unitCost = $variant['unit_cost'] ?? ($item['unit_cost'] ?? null);

                $stockTransaction->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $variant['product_variant_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $unitCost !== null ? $quantity * (float) $unitCost : null,
                    'remarks' => $variant['remarks'] ?? null,
                ]);
            }
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateTransaction(StockTransaction $stockTransaction, array $data = []): StockTransaction
    {
        DB::beginTransaction();

        try {
            if ($stockTransaction->status !== StockTransaction::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Stock Transactions can be Updated.'));
            }

            $type = $data['type'] ?? null;
            $items = $data['items'] ?? [];

            if ($type === StockTransaction::TYPE_TRANSFER) {
                $destinationStoreId = $data['destination_store_id'] ?? null;

                if (! $destinationStoreId || (int) $destinationStoreId === (int) ($data['store_id'] ?? null)) {
                    throw new GeneralException(__('Destination Store must be Different from the Source Store.'));
                }

                $linkedTransaction = $stockTransaction->linkedTransaction;

                if (! $linkedTransaction) {
                    throw new GeneralException(__('Linked Transfer Transaction was not Found.'));
                }

                // Normalize: $stockTransaction may be either the transfer_out or transfer_in side.
                $transferOut = $stockTransaction->type === StockTransaction::TYPE_TRANSFER_OUT ? $stockTransaction : $linkedTransaction;
                $transferIn = $stockTransaction->type === StockTransaction::TYPE_TRANSFER_IN ? $stockTransaction : $linkedTransaction;

                $transferOut->update([
                    'store_id' => $data['store_id'] ?? null,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'updated_by' => Auth::id(),
                ]);

                $transferIn->update([
                    'store_id' => $destinationStoreId,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'updated_by' => Auth::id(),
                ]);

                $transferOut->items()->delete();
                $transferIn->items()->delete();

                $this->createItems($transferOut, $items);
                $this->createItems($transferIn, $items);

                event(new StockTransactionUpdated($transferOut));
                event(new StockTransactionUpdated($transferIn));

                DB::commit();

                return $stockTransaction->refresh();
            }

            $purchaseFields = $type === StockTransaction::TYPE_PURCHASE
                ? $this->purchaseFields($data, $items, $stockTransaction->invoice_attachment_path)
                : [];

            $stockTransaction->update(array_merge($purchaseFields, [
                'type' => $type,
                'store_id' => $data['store_id'] ?? null,
                'supplier_id' => $type === StockTransaction::TYPE_PURCHASE ? ($data['supplier_id'] ?? null) : null,
                'transaction_date' => $data['transaction_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]));

            $stockTransaction->items()->delete();
            $this->createItems($stockTransaction, $items);

            event(new StockTransactionUpdated($stockTransaction));

            DB::commit();

            return $stockTransaction;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Stock Transaction.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateTransactionStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $stockTransaction = StockTransaction::findOrFail($id);

            $oldStatus = $stockTransaction->status;

            if ($oldStatus === $status) {
                throw new GeneralException(__('Stock Transaction is Already in this Status.'));
            }

            if ($status === StockTransaction::STATUS_APPROVED) {
                $this->approveTransaction($stockTransaction, $remarks);
            } else {
                $this->finalizeStatus($stockTransaction, $status, $remarks);

                if ($stockTransaction->isTransfer() && $stockTransaction->linked_transaction_id) {
                    $linkedTransaction = StockTransaction::find($stockTransaction->linked_transaction_id);

                    if ($linkedTransaction && $linkedTransaction->status === StockTransaction::STATUS_PENDING) {
                        $this->finalizeStatus($linkedTransaction, $status, $remarks);
                    }
                }
            }

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
            Log::error('Stock Transaction Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Stock Transaction Status Update'));
        }
    }

    /**
     * Approve a Stock Transaction: apply its Stock Effect, and — for a Transfer pair —
     * auto-approve the linked side too. Must run inside the caller's DB transaction.
     *
     * @throws GeneralException
     */
    protected function approveTransaction(StockTransaction $stockTransaction, ?string $remarks): void
    {
        if ($stockTransaction->type === StockTransaction::TYPE_TRANSFER_OUT) {
            $linkedTransaction = $stockTransaction->linked_transaction_id
                ? StockTransaction::findOrFail($stockTransaction->linked_transaction_id)
                : null;

            if (! $linkedTransaction) {
                throw new GeneralException(__('Linked Transfer-In Transaction was not Found.'));
            }

            // Apply the Subtract Effect at the Source Store First (guard checked inside).
            $this->applyStockEffect($stockTransaction);
            $this->finalizeStatus($stockTransaction, StockTransaction::STATUS_APPROVED, $remarks);

            // Auto-Approve the Linked Transfer-In: Add Effect at the Destination Store.
            $this->applyStockEffect($linkedTransaction);
            $this->finalizeStatus($linkedTransaction, StockTransaction::STATUS_APPROVED, $remarks);

            return;
        }

        if ($stockTransaction->type === StockTransaction::TYPE_TRANSFER_IN) {
            $linkedTransaction = $stockTransaction->linked_transaction_id
                ? StockTransaction::findOrFail($stockTransaction->linked_transaction_id)
                : null;

            if (! $linkedTransaction) {
                throw new GeneralException(__('Linked Transfer-Out Transaction was not Found.'));
            }

            // Apply the Subtract Effect at the Source (transfer_out) Side First (guard checked inside).
            $this->applyStockEffect($linkedTransaction);
            $this->finalizeStatus($linkedTransaction, StockTransaction::STATUS_APPROVED, $remarks);

            $this->applyStockEffect($stockTransaction);
            $this->finalizeStatus($stockTransaction, StockTransaction::STATUS_APPROVED, $remarks);

            return;
        }

        $this->applyStockEffect($stockTransaction);
        $this->finalizeStatus($stockTransaction, StockTransaction::STATUS_APPROVED, $remarks);
    }

    /**
     * Apply the Stock Effect of every Line Item on this Transaction to product_stocks.
     * Locks the affected product_stocks row(s) for the duration of the transaction.
     *
     * @throws GeneralException
     */
    protected function applyStockEffect(StockTransaction $stockTransaction): void
    {
        $isSubtract = in_array($stockTransaction->type, [StockTransaction::TYPE_ISSUE, StockTransaction::TYPE_TRANSFER_OUT], true);

        foreach ($stockTransaction->items as $item) {
            // Adjustment quantities may already be negative and are applied as-is;
            // every other type's delta is signed by whether it adds or subtracts.
            $delta = $stockTransaction->type === StockTransaction::TYPE_ADJUSTMENT
                ? (float) $item->quantity
                : (float) $item->quantity * ($isSubtract ? -1 : 1);

            $productStock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->where('store_id', $stockTransaction->store_id)
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
                    'store_id' => $stockTransaction->store_id,
                    'quantity' => 0,
                ]);
            }

            $newQuantity = (float) $productStock->quantity + $delta;

            if ($newQuantity < 0) {
                $product = $item->product()->first();
                $label = $product ? ($product->code.' — '.$product->name) : (string) $item->product_id;

                throw new GeneralException(__('Insufficient Stock for Product :label at this Store. Approval Cancelled.', ['label' => $label]));
            }

            $productStock->quantity = $newQuantity;
            $productStock->save();
        }
    }

    /**
     * Persist the Status change + ApprovalLog + Activity Log + Event for one Transaction row.
     * Does not apply any Stock Effect — that is the caller's responsibility on approve.
     */
    protected function finalizeStatus(StockTransaction $stockTransaction, string $status, ?string $remarks): void
    {
        $oldStatus = $stockTransaction->status;

        $stockTransaction->status = $status;
        $stockTransaction->saveQuietly();

        ApprovalLog::create([
            'model_type' => StockTransaction::class,
            'model_id' => $stockTransaction->id,
            'action_name' => $status,
            'actioned_by' => Auth::id(),
            'actioned_at' => now(),
            'remarks' => $remarks,
        ]);

        activity()
            ->performedOn($stockTransaction)
            ->causedBy(Auth::user())
            ->useLog('stock_transaction')
            ->event('statusUpdated')
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $status,
                'remarks' => $remarks,
            ])
            ->log('statusUpdated');

        event(new StockTransactionStatusUpdated($stockTransaction));
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyTransaction($id): bool
    {
        DB::beginTransaction();

        try {
            $stockTransaction = StockTransaction::findOrFail((int) $id);

            if ($stockTransaction->status === StockTransaction::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Stock Transaction cannot be Destroyed.'));
            }

            $stockTransaction->is_active = false;
            $stockTransaction->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($stockTransaction) {
                $stockTransaction->save();
            });

            $result = $stockTransaction->delete();

            event(new StockTransactionDestroyed($stockTransaction));

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
            Log::error('Stock Transaction Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Stock Transaction.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreTransaction($id): bool
    {
        DB::beginTransaction();
        try {

            $stockTransaction = StockTransaction::withTrashed()->findOrFail($id);

            $stockTransaction->is_active = true;
            $stockTransaction->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $stockTransaction->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $stockTransaction->restore();

            event(new StockTransactionRestored($stockTransaction));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Stock Transaction Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Stock Transaction.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteTransaction($id): bool
    {
        DB::beginTransaction();
        try {
            $stockTransaction = StockTransaction::withTrashed()->findOrFail($id);

            if ($stockTransaction->status === StockTransaction::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Stock Transaction cannot be Deleted.'));
            }

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($stockTransaction) {
                $stockTransaction->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('stock_transaction')
                ->event('forceDeleted')
                ->performedOn($stockTransaction)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new StockTransactionDeleted($stockTransaction));

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
            Log::error('Stock Transaction Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Stock Transaction.'));
        }
    }
}
