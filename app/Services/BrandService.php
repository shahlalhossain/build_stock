<?php

namespace App\Services;

use App\Events\Brand\BrandCreated;
use App\Events\Brand\BrandDestroyed;
use App\Events\Brand\BrandRestored;
use App\Events\Brand\BrandStatusUpdated;
use App\Events\Brand\BrandUpdated;
use App\Events\Brand\BrandDeleted;
use App\Models\ApprovalLog;
use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeneralException;
use Exception;
use Throwable;

/**
 * Class BrandService.
 */
class BrandService extends BaseService
{
    /**
     * BrandService Constructor.
     *
     * @param Brand $brand
     */
    public function __construct(Brand $brand)
    {
        $this->model = $brand;
    }

    /**
     * @param array $data
     * @return Brand
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeBrand(array $data = []) : Brand
    {
        DB::beginTransaction();
        try {
            $brandData = [
                'name'              => $data['name'] ?? null,
                'slug'              => $data['slug'] ?? null,
                'description'       => $data['description'] ?? null,
                'priority_order'    => $data['priority_order'] ?? null,
                'is_active'         => true,
                'created_by'        => Auth::id(),
                'updated_by'        => Auth::id(),
            ];
            $brand = $this->model::create($brandData);

            event(new BrandCreated($brand));

            DB::commit();
            return $brand;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Brand.'));
        }
    }

    /**
     * @param Brand $brand
     * @param array $data
     * @return Brand
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateBrand(Brand $brand, array $data = []) : Brand
    {
        DB::beginTransaction();

        try {
            $brand->update([
                'name'              => $data['name'] ?? null,
                'slug'              => $data['slug'] ?? null,
                'description'       => $data['description'] ?? null,
                'priority_order'    => $data['priority_order'] ?? null,
                'updated_by'        => Auth::id(),
            ]);

            event(new BrandUpdated($brand));

            DB::commit();
            return $brand;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Brand.'));
        }
    }

    /**
     * @param $id
     * @return bool
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateBrandStatus( int $id, string $status, ?string $remarks = null ): bool
    {
        DB::beginTransaction();
        try {

            $brand = Brand::findOrFail($id);

            $oldStatus = $brand->status;

            if ($oldStatus === $status) {
                return true;
            }

            // Update without Triggering Spatie's "updated" Activity Log
            $brand->status = $status;
            $result = $brand->saveQuietly();

            ApprovalLog::create([
                'model_type'    => Brand::class,
                'model_id'      => $brand->id,
                'action_name'   => $status,
                'actioned_by'   => Auth::id(),
                'actioned_at'   => now(),
                'remarks'       => $remarks
            ]);

            activity()
                ->performedOn($brand)
                ->causedBy(Auth::user())
                ->useLog('brand')
                ->event('statusUpdated')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'remarks'    => $remarks,
                ])
                ->log('statusUpdated');

            event(new BrandStatusUpdated($brand));

            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack(); throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Brand Status Update Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Brand Status Update'));
        }
    }

    /**
     * @param $id
     * @return bool
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyBrand($id) : bool
    {
        DB::beginTransaction();

        try {
            $brand = Brand::findOrFail((int)$id);

            $brand->is_active  = false;
            $brand->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($brand) {
                $brand->save();
            });

            $result = $brand->delete();

            event(new BrandDestroyed($brand));

            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Brand Destroy Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Brand.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreBrand($id) : bool
    {
        DB::beginTransaction();
        try {

            $brand = Brand::withTrashed()->findOrFail($id);

            $brand->is_active  = true;
            $brand->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $brand->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $brand->restore();

            event(new BrandRestored($brand));

            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Brand Restore Failed: ' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Brand.'));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteBrand($id) : bool
    {
        DB::beginTransaction();
        try {
            $brand = Brand::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($brand) {
                $brand->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('brand')
                ->event('forceDeleted')
                ->performedOn($brand)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new BrandDeleted($brand));

            DB::commit();
            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Brand Permanent Deletion Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Brand.'));
        }
    }
}
