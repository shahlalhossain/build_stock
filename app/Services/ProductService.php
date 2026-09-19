<?php

namespace App\Services;

use App\Events\Product\ProductCreated;
use App\Events\Product\ProductDeleted;
use App\Events\Product\ProductDestroyed;
use App\Events\Product\ProductRestored;
use App\Events\Product\ProductUpdated;
use App\Exceptions\GeneralException;
use App\Models\Product;
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
                'name' => $data['name'] ?? null,
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'has_variants' => $data['has_variants'] ?? false,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $product = $this->model::create($productData);

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
                'name' => $data['name'] ?? null,
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'has_variants' => $data['has_variants'] ?? false,
                'updated_by' => Auth::id(),
            ]);

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
