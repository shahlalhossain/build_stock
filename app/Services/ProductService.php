<?php

namespace App\Services;

use App\Events\Product\ProductCreated;
use App\Events\Product\ProductDeleted;
use App\Events\Product\ProductDestroyed;
use App\Events\Product\ProductRestored;
use App\Events\Product\ProductStatusUpdated;
use App\Events\Product\ProductUpdated;
use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockTransactionItem;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductService.
 */
class ProductService extends BaseService
{
    /**
     * ProductService Constructor.
     */
    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeProduct(array $data = []): Product
    {
        DB::beginTransaction();
        try {
            $productData = [
                'category_id' => $data['category_id'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'name' => $data['name'] ?? null,
                'code' => $this->generateCode(),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $product = $this->model::create($productData);

            $this->attachAttributeValues($product, $data['attribute_value_ids'] ?? []);
            $this->syncVariants($product, $data['variants'] ?? []);

            event(new ProductCreated($product));

            DB::commit();

            return $product;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Product.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateProduct(Product $product, array $data = []): Product
    {
        DB::beginTransaction();

        try {
            $product->update([
                'category_id' => $data['category_id'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'name' => $data['name'] ?? null,
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $this->attachAttributeValues($product, $data['attribute_value_ids'] ?? []);
            $this->syncVariants($product, $data['variants'] ?? []);

            event(new ProductUpdated($product));

            DB::commit();

            return $product;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Product.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateProductStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);

            $oldStatus = $product->status;

            if ($oldStatus === $status) {
                DB::rollBack();
                throw new GeneralException(__('Product is Already in this Status.'));
            }

            // Update without Triggering Spatie's "updated" Activity Log
            $product->status = $status;
            $result = $product->saveQuietly();

            ApprovalLog::create([
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($product)
                ->causedBy(Auth::user())
                ->useLog('product')
                ->event('statusUpdated')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'remarks' => $remarks,
                ])
                ->log('statusUpdated');

            event(new ProductStatusUpdated($product));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Product Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Product Status Update'));
        }
    }

    /**
     * Add the submitted attribute-value ids to the product's existing
     * assignments (merge/add-only) rather than replacing them, so
     * updating a Product never silently drops previously-saved
     * specifications that the current form submission didn't resend.
     */
    protected function attachAttributeValues(Product $product, array $attributeValueIds): void
    {
        $ids = array_filter($attributeValueIds);

        if (empty($ids)) {
            return;
        }

        $product->attributeValues()->syncWithoutDetaching($ids);
    }

    /**
     * Delete-all-and-recreate the Product's Variants (matching this app's
     * existing sub-resource sync convention, e.g. Supplier's
     * Contacts/Addresses). Absent/empty $rows is fully valid — a Product
     * may have zero Variants.
     *
     * Variants already referenced by Stock Transactions/Stocks cannot be
     * hard-deleted (stock_transaction_items.product_variant_id has no
     * cascade, unlike the pivot table), so those are soft-deleted with
     * their pivot rows detached instead, to avoid an FK violation while
     * still fully replacing the Variant's own data on the next line.
     */
    protected function syncVariants(Product $product, array $rows): void
    {
        $product->variants()->get()->each(function (ProductVariant $variant) {
            $isReferenced = $variant->stocks()->exists()
                || StockTransactionItem::where('product_variant_id', $variant->id)->exists();

            $variant->attributeValues()->detach();

            if ($isReferenced) {
                $variant->delete();

                return;
            }

            $variant->forceDelete();
        });

        foreach ($rows as $row) {
            $sku = $row['sku'] ?? null;

            if (! $sku) {
                continue;
            }

            $variant = $product->variants()->create([
                'sku' => $sku,
                'variant_name' => $row['variant_name'] ?? null,
                'unit_price' => $row['unit_price'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $attributeValueIds = array_filter($row['attribute_value_ids'] ?? []);

            if (! empty($attributeValueIds)) {
                $variant->attributeValues()->sync($attributeValueIds);
            }
        }
    }

    /**
     * Generate the next Sequential Product Code (e.g. PRD-0001).
     */
    protected function generateCode(): string
    {
        $lastNumber = Product::withTrashed()
            ->where('code', 'like', 'PRD-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_number')
            ->value('max_number');

        return 'PRD-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyProduct($id): bool
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail((int) $id);

            $product->is_active = false;
            $product->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($product) {
                $product->save();
            });

            $result = $product->delete();

            event(new ProductDestroyed($product));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Product Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Product.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreProduct($id): bool
    {
        DB::beginTransaction();
        try {

            $product = Product::withTrashed()->findOrFail($id);

            $product->is_active = true;
            $product->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $product->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $product->restore();

            event(new ProductRestored($product));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Product Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Product.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteProduct($id): bool
    {
        DB::beginTransaction();
        try {
            $product = Product::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($product) {
                $product->attributeValues()->detach();
                $product->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('product')
                ->event('forceDeleted')
                ->performedOn($product)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new ProductDeleted($product));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Product Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Product.'));
        }
    }
}
