<?php

namespace App\Services;

use App\Events\Store\StoreCreated;
use App\Events\Store\StoreDeleted;
use App\Events\Store\StoreDestroyed;
use App\Events\Store\StoreRestored;
use App\Events\Store\StoreStatusUpdated;
use App\Events\Store\StoreUpdated;
use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Store;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class StoreService.
 */
class StoreService extends BaseService
{
    /**
     * StoreService Constructor.
     */
    public function __construct(Store $store)
    {
        $this->model = $store;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeStore(array $data = []): Store
    {
        DB::beginTransaction();
        try {
            $storeData = [
                'project_id' => $data['project_id'] ?? null,
                'name' => $data['name'] ?? null,
                'code' => $this->generateCode($data['type'] ?? Store::TYPE_STORE),
                'type' => $data['type'] ?? null,
                'description' => $data['description'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'storekeeper_id' => $data['storekeeper_id'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $store = $this->model::create($storeData);

            event(new StoreCreated($store));

            DB::commit();

            return $store;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Store.'));
        }
    }

    /**
     * Generate the next Sequential Code for the given Type (e.g. STR-0001, WH-0001).
     */
    protected function generateCode(string $type): string
    {
        $prefix = $type === Store::TYPE_WAREHOUSE ? 'WH' : 'STR';

        $lastNumber = Store::withTrashed()
            ->where('type', $type)
            ->where('code', 'like', $prefix.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, '.(strlen($prefix) + 2).') AS UNSIGNED)) as max_number')
            ->value('max_number');

        return $prefix.'-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateStore(Store $store, array $data = []): Store
    {
        DB::beginTransaction();

        try {
            $store->update([
                'project_id' => $data['project_id'] ?? null,
                'name' => $data['name'] ?? null,
                'type' => $data['type'] ?? null,
                'description' => $data['description'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'storekeeper_id' => $data['storekeeper_id'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new StoreUpdated($store));

            DB::commit();

            return $store;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Store.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateStoreStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {
            $store = Store::findOrFail($id);

            $oldStatus = $store->status;

            if ($oldStatus === $status) {
                return true;
            }

            // Update without Triggering Spatie's "updated" Activity Log
            $store->status = $status;
            $result = $store->saveQuietly();

            ApprovalLog::create([
                'model_type' => Store::class,
                'model_id' => $store->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($store)
                ->causedBy(Auth::user())
                ->useLog('store')
                ->event('statusUpdated')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'remarks' => $remarks,
                ])
                ->log('statusUpdated');

            event(new StoreStatusUpdated($store));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Store Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Store Status Update'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyStore($id): bool
    {
        DB::beginTransaction();

        try {
            $store = Store::findOrFail((int) $id);

            $store->is_active = false;
            $store->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($store) {
                $store->save();
            });

            $result = $store->delete();

            event(new StoreDestroyed($store));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Store Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Store.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreStore($id): bool
    {
        DB::beginTransaction();
        try {

            $store = Store::withTrashed()->findOrFail($id);

            $store->is_active = true;
            $store->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $store->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $store->restore();

            event(new StoreRestored($store));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Store Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Store.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteStore($id): bool
    {
        DB::beginTransaction();
        try {
            $store = Store::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($store) {
                $store->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('store')
                ->event('forceDeleted')
                ->performedOn($store)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new StoreDeleted($store));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Store Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Store.'));
        }
    }
}
