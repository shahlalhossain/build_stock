<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\ProductRequisition;
use App\Models\ProductRequisitionItem;
use App\Models\ProductStock;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class ProductPurchaseService.
 */
class ProductPurchaseService extends BaseService
{
    protected UnitConversionService $unitConversionService;

    /**
     * ProductPurchaseService Constructor.
     */
    public function __construct(ProductPurchase $productPurchase, UnitConversionService $unitConversionService)
    {
        $this->model = $productPurchase;
        $this->unitConversionService = $unitConversionService;
    }

    /**
     * Generate the next Sequential Code (e.g. PUR-0001).
     */
    protected function generateCode(): string
    {
        $prefix = 'PUR';

        $lastNumber = ProductPurchase::withTrashed()
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Load a Requisition eligible for "Purchase against Requisition" — must be
     * Approved and have at least one Line Item with Remaining Quantity.
     *
     * @throws GeneralException
     */
    public function getRequisitionForPurchase(int $requisitionId): ProductRequisition
    {
        $requisition = ProductRequisition::query()
            ->availableForPurchase()
            ->with(['store', 'items.product', 'items.productVariant.attributeValues', 'items.unit'])
            ->find($requisitionId);

        if (! $requisition) {
            throw new GeneralException(__('Requisition is not Available for Purchase (Not Approved, or Already Fully Purchased).'));
        }

        return $requisition;
    }

    /**
     * Validate every submitted Item Line against its linked Requisition Item's
     * Remaining Quantity. Used only for the "Purchase against Requisition" Flow.
     *
     * @throws GeneralException
     */
    protected function assertItemsWithinRequisitionRemaining(int $requisitionId, array $items, ?int $excludePurchaseId = null): void
    {
        $requisitionItemIds = array_filter(array_column($items, 'requisition_item_id'));

        if (empty($requisitionItemIds)) {
            return;
        }

        $requisitionItems = ProductRequisitionItem::query()
            ->where('product_requisition_id', $requisitionId)
            ->whereIn('id', $requisitionItemIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $requestedByLine = [];
        foreach ($items as $item) {
            $requisitionItemId = $item['requisition_item_id'] ?? null;
            if (! $requisitionItemId) {
                continue;
            }

            if (! $requisitionItems->has($requisitionItemId)) {
                throw new GeneralException(__('One or More Selected Products do not Belong to this Requisition.'));
            }

            $requestedByLine[$requisitionItemId] = ($requestedByLine[$requisitionItemId] ?? 0) + (float) ($item['quantity'] ?? 0);
        }

        foreach ($requestedByLine as $requisitionItemId => $requestedQuantity) {
            /** @var ProductRequisitionItem $requisitionItem */
            $requisitionItem = $requisitionItems->get($requisitionItemId);

            $alreadyApprovedElsewhere = $requisitionItem->purchaseItems()
                ->whereHas('productPurchase', function ($query) use ($excludePurchaseId) {
                    $query->where('status', ProductPurchase::STATUS_APPROVED);
                    if ($excludePurchaseId) {
                        $query->where('id', '!=', $excludePurchaseId);
                    }
                })
                ->sum('quantity');

            $remaining = (float) $requisitionItem->quantity - (float) $alreadyApprovedElsewhere;

            if ($requestedQuantity > $remaining + 0.0001) {
                throw new GeneralException(__(
                    'Requested Quantity for :product Exceeds the Remaining Requisition Quantity (:remaining Remaining).',
                    ['product' => $requisitionItem->product?->name ?? __('Selected Product'), 'remaining' => $remaining]
                ));
            }
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storePurchase(array $data = []): ProductPurchase
    {
        $uploadedAttachmentPath = null;

        DB::beginTransaction();
        try {
            $items = $data['items'] ?? [];
            $uploadedAttachmentPath = $this->storeInvoiceAttachment($data['invoice_attachment'] ?? null);

            if (! empty($data['requisition_id'])) {
                $this->assertItemsWithinRequisitionRemaining((int) $data['requisition_id'], $items);
            }

            $productPurchase = $this->model::create(array_merge(
                $this->purchaseFields($data, $items, $uploadedAttachmentPath),
                [
                    'code' => $this->generateCode(),
                    'requisition_id' => $data['requisition_id'] ?? null,
                    'store_id' => $data['store_id'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            ));

            $this->createItems($productPurchase, $items);

            DB::commit();

            return $productPurchase;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw new GeneralException(__('There was a Problem on Creating New Purchase.'));
        }
    }

    /**
     * Store the Optional Invoice Attachment on the private "local" Disk.
     */
    protected function storeInvoiceAttachment(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('invoices/product-purchases', 'local');
    }

    protected function deleteInvoiceAttachment(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Compute the Header Fields: per-Item Line Totals feed total_amount,
     * Discount/Tax then derive net_amount.
     */
    protected function purchaseFields(array $data, array $items, ?string $uploadedAttachmentPath): array
    {
        $totalAmount = $this->calculateItemsTotal($items);

        $discountType = $data['discount_type'] ?? null;
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $taxAmount = (float) ($data['tax_amount'] ?? 0);

        $discountValue = $discountType === ProductPurchase::DISCOUNT_TYPE_PERCENTAGE
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
            'payment_status' => $data['payment_status'] ?? ProductPurchase::PAYMENT_STATUS_UNPAID,
            'paid_amount' => (float) ($data['paid_amount'] ?? 0),
        ];
    }

    /**
     * Sum every Line Item's Total (Quantity x Unit Cost).
     */
    protected function calculateItemsTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_cost'] ?? 0);
        }

        return $total;
    }

    /**
     * Create the Line Items for a given Purchase.
     */
    protected function createItems(ProductPurchase $productPurchase, array $items): void
    {
        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitCost = $item['unit_cost'] ?? null;

            $productPurchase->items()->create([
                'requisition_item_id' => $item['requisition_item_id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $unitCost !== null ? $quantity * (float) $unitCost : null,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updatePurchase(ProductPurchase $productPurchase, array $data = []): ProductPurchase
    {
        $uploadedAttachmentPath = null;
        $oldAttachmentPath = $productPurchase->invoice_attachment_path;

        DB::beginTransaction();
        try {
            if ($productPurchase->status !== ProductPurchase::STATUS_PENDING) {
                throw new GeneralException(__('Only Pending Purchases can be Updated.'));
            }

            $items = $data['items'] ?? [];
            $requisitionId = $data['requisition_id'] ?? $productPurchase->requisition_id;

            if ($requisitionId) {
                $this->assertItemsWithinRequisitionRemaining((int) $requisitionId, $items, $productPurchase->id);
            }

            $newAttachmentPath = $oldAttachmentPath;
            if (! empty($data['invoice_attachment'])) {
                $uploadedAttachmentPath = $this->storeInvoiceAttachment($data['invoice_attachment']);
                $newAttachmentPath = $uploadedAttachmentPath;
            }

            $productPurchase->update(array_merge(
                $this->purchaseFields($data, $items, $newAttachmentPath),
                [
                    'requisition_id' => $data['requisition_id'] ?? null,
                    'store_id' => $data['store_id'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'transaction_date' => $data['transaction_date'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'updated_by' => Auth::id(),
                ]
            ));

            $productPurchase->items()->delete();
            $this->createItems($productPurchase, $items);

            // Only remove the OLD file after the new state is safely committed-pending.
            if ($uploadedAttachmentPath && $oldAttachmentPath && $oldAttachmentPath !== $uploadedAttachmentPath) {
                Storage::disk('local')->delete($oldAttachmentPath);
            }

            DB::commit();

            return $productPurchase;
        } catch (GeneralException $generalException) {
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw $generalException;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            $this->deleteInvoiceAttachment($uploadedAttachmentPath);
            throw new GeneralException(__('There was a Problem on Updating the Purchase.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updatePurchaseStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $productPurchase = ProductPurchase::findOrFail($id);

            if ($productPurchase->status === $status) {
                throw new GeneralException(__('Purchase is Already in this Status.'));
            }

            if ($status === ProductPurchase::STATUS_APPROVED) {
                if ($productPurchase->requisition_id) {
                    $items = $productPurchase->items()
                        ->whereNotNull('requisition_item_id')
                        ->get(['requisition_item_id', 'quantity'])
                        ->map(fn ($item) => ['requisition_item_id' => $item->requisition_item_id, 'quantity' => $item->quantity])
                        ->all();

                    $this->assertItemsWithinRequisitionRemaining($productPurchase->requisition_id, $items, $productPurchase->id);
                }

                $this->applyStockEffect($productPurchase);
            }

            $oldStatus = $productPurchase->status;
            $productPurchase->status = $status;
            $productPurchase->saveQuietly();

            ApprovalLog::create([
                'model_type' => ProductPurchase::class,
                'model_id' => $productPurchase->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($productPurchase)
                ->causedBy(Auth::user())
                ->useLog('product_purchase')
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
            Log::error('Purchase Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Purchase Status Update'));
        }
    }

    /**
     * Apply the Stock Effect of every Line Item to product_stocks — a Purchase
     * always ADDS Stock at its own Store, normalized to the Product's base Unit
     * via UnitConversionService. Must run inside the caller's DB Transaction.
     *
     * @throws GeneralException
     */
    protected function applyStockEffect(ProductPurchase $productPurchase): void
    {
        foreach ($productPurchase->items as $item) {
            $product = $item->product ?: Product::findOrFail($item->product_id);

            $baseQuantity = $this->unitConversionService->toBaseUnit($product, $item->unit_id, (float) $item->quantity);

            $productStock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->where('store_id', $productPurchase->store_id)
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
                    'store_id' => $productPurchase->store_id,
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
    public function destroyPurchase($id): bool
    {
        DB::beginTransaction();

        try {
            $productPurchase = ProductPurchase::findOrFail((int) $id);

            if ($productPurchase->status === ProductPurchase::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Purchase cannot be Destroyed.'));
            }

            $productPurchase->is_active = false;
            $productPurchase->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($productPurchase) {
                $productPurchase->save();
            });

            $result = $productPurchase->delete();

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
            Log::error('Purchase Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Purchase.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restorePurchase($id): bool
    {
        DB::beginTransaction();
        try {
            $productPurchase = ProductPurchase::withTrashed()->findOrFail($id);

            $productPurchase->is_active = true;
            $productPurchase->deleted_by = null;

            $productPurchase->saveQuietly();

            $result = $productPurchase->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Purchase Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Purchase.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deletePurchase($id): bool
    {
        DB::beginTransaction();
        try {
            $productPurchase = ProductPurchase::withTrashed()->findOrFail($id);

            if ($productPurchase->status === ProductPurchase::STATUS_APPROVED) {
                throw new GeneralException(__('An Approved Purchase cannot be Deleted.'));
            }

            if ($productPurchase->invoice_attachment_path) {
                Storage::disk('local')->delete($productPurchase->invoice_attachment_path);
            }

            activity()->withoutLogs(function () use ($productPurchase) {
                $productPurchase->forceDelete();
            });

            activity()
                ->useLog('product_purchase')
                ->event('forceDeleted')
                ->performedOn($productPurchase)
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
            Log::error('Purchase Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Purchase.'));
        }
    }
}
