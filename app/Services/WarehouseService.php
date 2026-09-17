<?php

namespace App\Services;

use App\Events\Warehouse\WarehouseCreated;
use App\Events\Warehouse\WarehouseDeleted;
use App\Events\Warehouse\WarehouseDestroyed;
use App\Events\Warehouse\WarehouseRestored;
use App\Events\Warehouse\WarehouseUpdated;
use App\Exceptions\GeneralException;
use App\Models\Warehouse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class WarehouseService.
 */
class WarehouseService extends BaseService
{
    /**
     * WarehouseService Constructor.
     */
    public function __construct(Warehouse $warehouse)
    {
        $this->model = $warehouse;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeWarehouse(array $data = []): Warehouse
    {
        DB::beginTransaction();
        try {
            $warehouseData = [
                'project_id' => $data['project_id'] ?? null,
                'name' => $data['name'] ?? null,
                'code' => $data['code'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $warehouse = $this->model::create($warehouseData);

            event(new WarehouseCreated($warehouse));

            DB::commit();

            return $warehouse;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Warehouse.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateWarehouse(Warehouse $warehouse, array $data = []): Warehouse
    {
        DB::beginTransaction();

        try {
            $warehouse->update([
                'project_id' => $data['project_id'] ?? null,
                'name' => $data['name'] ?? null,
                'code' => $data['code'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new WarehouseUpdated($warehouse));

            DB::commit();

            return $warehouse;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Warehouse.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyWarehouse($id): bool
    {
        DB::beginTransaction();

        try {
            $warehouse = Warehouse::findOrFail((int) $id);

            $warehouse->is_active = false;
            $warehouse->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($warehouse) {
                $warehouse->save();
            });

            $result = $warehouse->delete();

            event(new WarehouseDestroyed($warehouse));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Warehouse Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Warehouse.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreWarehouse($id): bool
    {
        DB::beginTransaction();
        try {

            $warehouse = Warehouse::withTrashed()->findOrFail($id);

            $warehouse->is_active = true;
            $warehouse->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $warehouse->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $warehouse->restore();

            event(new WarehouseRestored($warehouse));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Warehouse Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Warehouse.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteWarehouse($id): bool
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($warehouse) {
                $warehouse->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('warehouse')
                ->event('forceDeleted')
                ->performedOn($warehouse)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new WarehouseDeleted($warehouse));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Warehouse Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Warehouse.'));
        }
    }
}
