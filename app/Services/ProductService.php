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
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
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
            $this->generateVariantsFromAttributeValues($product);

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
            $this->generateVariantsFromAttributeValues($product);

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
     * Replace the Product's Specification Attribute Values with the submitted set —
     * a full sync, not add-only: the Create/Edit form always resends every currently
     * checked Value, and this is now the sole source Variants are generated from (see
     * generateVariantsFromAttributeValues), so unchecking a Value must actually detach
     * it or a Product's Variant set could never shrink.
     *
     * product_attribute_values also stores attribute_id directly (alongside
     * attribute_value_id) so a row's Attribute is readable without joining
     * through attribute_values — resolved here from each submitted Value.
     */
    protected function attachAttributeValues(Product $product, array $attributeValueIds): void
    {
        $ids = array_filter($attributeValueIds);

        $attributeIdsByValueId = AttributeValue::whereIn('id', $ids)->pluck('attribute_id', 'id');

        $syncData = collect($ids)
            ->mapWithKeys(fn ($valueId) => [$valueId => ['attribute_id' => $attributeIdsByValueId[$valueId]]])
            ->all();

        $product->attributeValues()->sync($syncData);
    }

    /**
     * Generate the Product's Variants automatically from its own Specification
     * Attribute Values — the full cartesian product across every distinct Attribute
     * that currently has at least one checked Value (e.g. Color: White/Blue/Black x
     * Grade: A/B/C x Thickness: 12mm/18mm/25mm -> 27 Variants). There is no separate
     * Variant UI (Sections 36-37): this runs automatically on every Product save,
     * right after attachAttributeValues() has synced the current Specification set.
     *
     * A Product with Values checked under only ONE Attribute (or none) has no
     * meaningful combination to build and is kept variant-less (Section 12 — e.g.
     * Construction Sand tagged only with a single Grade stays a plain Product).
     *
     * Matching against what already exists is by the NORMALIZED Attribute-Value set,
     * not by id or row order: regenerating after checking one more Value must not
     * churn the SKUs or lose the Stock/Transaction history of combinations that still
     * exist, and must not create duplicates of them either (Section 28 — order of
     * Values never affects a combination's identity). A combination with no existing
     * match (active or previously soft-deleted) is created fresh, auto-named and
     * auto-SKU'd; an existing Variant whose combination is no longer produced is
     * soft-deleted (never hard-deleted — see destroyVariant for the stock-referenced
     * guard this preserves).
     *
     * @throws GeneralException
     */
    protected function generateVariantsFromAttributeValues(Product $product): void
    {
        $groups = $product->attributeValues()
            ->get()
            ->groupBy('attribute_id')
            ->map(fn ($values) => $values->pluck('id')->all())
            ->values()
            ->all();

        $combinations = count($groups) >= 2 ? $this->cartesianProduct($groups) : [];

        $existingByCombination = $product->variants()
            ->withTrashed()
            ->with('attributeValues')
            ->get()
            ->keyBy(fn (ProductVariant $variant) => $this->combinationKey($variant->attributeValues->pluck('id')->all()));

        $matchedIds = [];
        $nextSkuSequence = null;

        foreach ($combinations as $attributeValueIds) {
            $productVariant = $existingByCombination->get($this->combinationKey($attributeValueIds));

            if (! $productVariant) {
                $productVariant = new ProductVariant(['product_id' => $product->id]);
            } elseif ($productVariant->trashed()) {
                $productVariant->deleted_at = null;
                $productVariant->deleted_by = null;
                $productVariant->is_active = true;
            }

            $sku = $productVariant->sku;

            if (! $sku) {
                $nextSkuSequence ??= $this->nextVariantSkuSequence($product);
                $sku = $product->code.'-'.str_pad((string) $nextSkuSequence, 3, '0', STR_PAD_LEFT);
                $nextSkuSequence++;
            }

            $productVariant->fill([
                'variant_name' => $this->generateVariantName($attributeValueIds),
                'sku' => $sku,
                'is_active' => true,
                'updated_by' => Auth::id(),
            ]);

            if (! $productVariant->exists) {
                $productVariant->created_by = Auth::id();
            }

            $productVariant->save();

            $productVariant->attributeValues()->sync($attributeValueIds);

            $matchedIds[] = $productVariant->id;
        }

        $product->variants()
            ->whereNotIn('id', $matchedIds)
            ->get()
            ->each(fn (ProductVariant $variant) => $this->destroyVariant($variant));

        $product->updateQuietly(['has_variants' => ! empty($matchedIds)]);
    }

    /**
     * Cartesian product across Attribute groups, each an array of Attribute-Value ids
     * (e.g. [[white,blue,black], [gradeA,gradeB,gradeC]] -> every [color, grade] pair).
     *
     * @param  array<int, array<int, int>>  $groups
     * @return array<int, array<int, int>>
     */
    protected function cartesianProduct(array $groups): array
    {
        return array_reduce(
            $groups,
            function (array $combinations, array $group) {
                $next = [];

                foreach ($combinations as $combination) {
                    foreach ($group as $valueId) {
                        $next[] = [...$combination, $valueId];
                    }
                }

                return $next;
            },
            [[]]
        );
    }

    /**
     * Order-independent identity for an Attribute-Value combination (Section 28) —
     * used to match a freshly-generated combination back to an existing Variant.
     */
    protected function combinationKey(array $attributeValueIds): string
    {
        sort($attributeValueIds);

        return implode(',', $attributeValueIds);
    }

    /**
     * Next free numeric suffix for this Product's auto-generated Variant SKUs
     * (e.g. product code PRD-0125 -> PRD-0125-001, ...-002, ...), continuing past
     * the highest existing suffix (including Trashed Variants) so a regenerate
     * never reissues an SKU still held by a restored or soft-deleted row.
     */
    protected function nextVariantSkuSequence(Product $product): int
    {
        $prefix = $product->code.'-';

        $lastNumber = ProductVariant::withTrashed()
            ->where('product_id', $product->id)
            ->where('sku', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(sku, ?) AS UNSIGNED)) as max_number', [strlen($prefix) + 1])
            ->value('max_number');

        return (int) $lastNumber + 1;
    }

    /**
     * Derive a human-readable Variant Name from its Attribute Values, slash-joined in
     * Attribute-id order (e.g. "White / Grade A / 12mm") — the actual source of truth
     * remains product_variant_values, not this string.
     */
    protected function generateVariantName(array $attributeValueIds): string
    {
        return AttributeValue::whereIn('id', $attributeValueIds)
            ->orderBy('attribute_id')
            ->pluck('value')
            ->implode(' / ');
    }

    /**
     * Soft-delete a Variant no longer referenced by the submitted set. Stock/Transaction
     * history (if any) keeps pointing at this row via product_variant_id, matching the
     * Product-level destroy convention (status/soft-delete, never a hard delete here).
     */
    protected function destroyVariant(ProductVariant $variant): void
    {
        $variant->is_active = false;
        $variant->deleted_by = Auth::id();
        $variant->save();
        $variant->delete();
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
