<?php

namespace App\Services;

use App\Events\Brand\BrandCreated;
use App\Events\Brand\BrandUpdated;
use App\Events\Brand\BrandDeleted;
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
            DB::commit();
            //event(new BrandCreated($brand));
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Brand.'));
        }
        return $brand;
    }

    public function updateBrand(Brand $brand, array $data = []) : Brand
    {
        DB::beginTransaction();

        try {
            $brand->update([
                'name'              => $data['name'] ?? null,
                'slug'              => $data['slug'] ?? null,
                'description'       => $data['description'] ?? null,
                'priority_order'    => $data['priority_order'] ?? null,
                'updated_by'    => Auth::id(),
            ]);

            DB::commit();

        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Brand.'));
        }

        return $brand;
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

            $brand->is_active   = false;
            $brand->deleted_by  = Auth::id();
            $brand->deleted_at  = now();

            $result = $brand->save();
            // event(new BrandDestroyed($brand));
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
            $brand->restore();
            $brand->is_active = true;
            $brand->deleted_by = null;

            $result = $brand->save();
            // event(new BrandRestored($brand));
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
            $result = $brand->forceDelete();
            DB::commit();
            // event(new BrandDeleted($brand));
            return $result;
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
