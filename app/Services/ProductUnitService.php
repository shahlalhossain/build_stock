<?php

namespace App\Services;

use App\Events\ProductUnit\ProductUnitCreated;
use App\Events\ProductUnit\ProductUnitDeleted;
use App\Events\ProductUnit\ProductUnitDestroyed;
use App\Events\ProductUnit\ProductUnitRestored;
use App\Events\ProductUnit\ProductUnitUpdated;
use App\Exceptions\GeneralException;
use App\Models\ProductUnit;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductUnitService.
 */
class ProductUnitService extends BaseService
{
    /**
     * ProductUnitService Constructor.
     */
    public function __construct(ProductUnit $productUnit)
    {
        $this->model = $productUnit;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeProductUnit(array $data = []): ProductUnit
    {
        DB::beginTransaction();
        try {
            $productUnitData = [
                'group' => $data['group'] ?? null,
                'name' => $data['name'] ?? null,
                'symbol' => $data['symbol'] ?? null,
                'description' => $data['description'] ?? null,
                'usage' => $data['usage'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $productUnit = $this->model::create($productUnitData);

            event(new ProductUnitCreated($productUnit));

            DB::commit();

            return $productUnit;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Product-Unit.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateProductUnit(ProductUnit $productUnit, array $data = []): ProductUnit
    {
        DB::beginTransaction();

        try {
            $productUnit->update([
                'group' => $data['group'] ?? null,
                'name' => $data['name'] ?? null,
                'symbol' => $data['symbol'] ?? null,
                'description' => $data['description'] ?? null,
                'usage' => $data['usage'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new ProductUnitUpdated($productUnit));

            DB::commit();

            return $productUnit;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Product-Unit.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyProductUnit($id): bool
    {
        DB::beginTransaction();

        try {
            $productUnit = ProductUnit::findOrFail((int) $id);

            $productUnit->is_active = false;
            $productUnit->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($productUnit) {
                $productUnit->save();
            });

            $result = $productUnit->delete();

            event(new ProductUnitDestroyed($productUnit));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('ProductUnit Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Product-Unit.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreProductUnit($id): bool
    {
        DB::beginTransaction();
        try {

            $productUnit = ProductUnit::withTrashed()->findOrFail($id);

            $productUnit->is_active = true;
            $productUnit->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $productUnit->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $productUnit->restore();

            event(new ProductUnitRestored($productUnit));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('ProductUnit Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Product-Unit.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteProductUnit($id): bool
    {
        DB::beginTransaction();
        try {
            $productUnit = ProductUnit::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($productUnit) {
                $productUnit->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('product_unit')
                ->event('forceDeleted')
                ->performedOn($productUnit)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new ProductUnitDeleted($productUnit));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('ProductUnit Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Product-Unit.'));
        }
    }
}
