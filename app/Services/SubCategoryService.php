<?php

namespace App\Services;

use App\Events\SubCategory\SubCategoryCreated;
use App\Events\SubCategory\SubCategoryDeleted;
use App\Events\SubCategory\SubCategoryDestroyed;
use App\Events\SubCategory\SubCategoryRestored;
use App\Events\SubCategory\SubCategoryUpdated;
use App\Exceptions\GeneralException;
use App\Models\SubCategory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class SubCategoryService.
 */
class SubCategoryService extends BaseService
{
    /**
     * SubCategoryService Constructor.
     */
    public function __construct(SubCategory $subCategory)
    {
        $this->model = $subCategory;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeSubCategory(array $data = []): SubCategory
    {
        DB::beginTransaction();
        try {
            $subCategoryData = [
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $subCategory = $this->model::create($subCategoryData);

            event(new SubCategoryCreated($subCategory));

            DB::commit();

            return $subCategory;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Sub-Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateSubCategory(SubCategory $subCategory, array $data = []): SubCategory
    {
        DB::beginTransaction();

        try {
            $subCategory->update([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new SubCategoryUpdated($subCategory));

            DB::commit();

            return $subCategory;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Sub-Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroySubCategory($id): bool
    {
        DB::beginTransaction();

        try {
            $subCategory = SubCategory::findOrFail((int) $id);

            $subCategory->is_active = false;
            $subCategory->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($subCategory) {
                $subCategory->save();
            });

            $result = $subCategory->delete();

            event(new SubCategoryDestroyed($subCategory));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('SubCategory Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Sub-Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreSubCategory($id): bool
    {
        DB::beginTransaction();
        try {

            $subCategory = SubCategory::withTrashed()->findOrFail($id);

            $subCategory->is_active = true;
            $subCategory->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $subCategory->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $subCategory->restore();

            event(new SubCategoryRestored($subCategory));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('SubCategory Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Sub-Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteSubCategory($id): bool
    {
        DB::beginTransaction();
        try {
            $subCategory = SubCategory::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($subCategory) {
                $subCategory->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('sub_category')
                ->event('forceDeleted')
                ->performedOn($subCategory)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new SubCategoryDeleted($subCategory));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('SubCategory Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Sub-Category.'));
        }
    }
}
