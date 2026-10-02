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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class StoreService.
 */
class StoreService extends BaseService
{
    protected ImageService $imageService;

    /**
     * StoreService Constructor.
     */
    public function __construct(Store $store, ImageService $imageService)
    {
        $this->model = $store;
        $this->imageService = $imageService;
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
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $storeData['image'] = $this->imageService->uploadImage($data['image'], 'store.image');
            }

            $store = $this->model::create($storeData);

            $this->syncManagersAndStorekeepers($store, $data);

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
     * Sync the store_user Pivot from the Multi-Select Form Inputs — one Row
     * per (User, Slot) Pair. A User picked in BOTH Lists gets both Slot Rows.
     *
     * NOTE: BelongsToMany::sync() ignores extra wherePivot() Constraints when
     * Detaching (it only Scopes by the Foreign Key), so syncing "managers()"
     * then "storekeepers()" on the same Pivot Table would let the second
     * sync() wipe out Rows the first one just wrote. Writing the Pivot Rows
     * Directly avoids that.
     */
    protected function syncManagersAndStorekeepers(Store $store, array $data): void
    {
        $managerIds = array_values(array_unique(array_filter($data['manager_ids'] ?? [])));
        $storekeeperIds = array_values(array_unique(array_filter($data['storekeeper_ids'] ?? [])));

        DB::table('store_user')->where('store_id', $store->id)->delete();

        $now = now();
        $rows = [];

        foreach ($managerIds as $userId) {
            $rows[] = ['store_id' => $store->id, 'user_id' => $userId, 'role_type' => 'manager', 'created_at' => $now, 'updated_at' => $now];
        }

        foreach ($storekeeperIds as $userId) {
            $rows[] = ['store_id' => $store->id, 'user_id' => $userId, 'role_type' => 'storekeeper', 'created_at' => $now, 'updated_at' => $now];
        }

        if (! empty($rows)) {
            DB::table('store_user')->insert($rows);
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
            $storeData = [
                'project_id' => $data['project_id'] ?? null,
                'name' => $data['name'] ?? null,
                'type' => $data['type'] ?? null,
                'description' => $data['description'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'updated_by' => Auth::id(),
            ];

            // Remove Image (UI Checkbox) — Skipped Entirely if a New Image is also
            // Uploaded in the same Request, since the New-Image Branch below already
            // Deletes the Old File before Storing the Replacement.
            if (! empty($data['remove_image']) && empty($data['image']) && $store->image) {
                if (Storage::disk('public')->exists($store->image)) {
                    Storage::disk('public')->delete($store->image);
                }
                $storeData['image'] = null;
            }

            // Replace Image
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($store->image && Storage::disk('public')->exists($store->image)) {
                    Storage::disk('public')->delete($store->image);
                }
                $storeData['image'] = $this->imageService->uploadImage($data['image'], 'store.image');
            }

            $store->update($storeData);

            $this->syncManagersAndStorekeepers($store, $data);

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
                DB::rollBack();
                throw new GeneralException(__('Store is Already in this Status.'));
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
