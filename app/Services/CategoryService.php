<?php

namespace App\Services;

use App\Events\Category\CategoryCreated;
use App\Events\Category\CategoryDeleted;
use App\Events\Category\CategoryDestroyed;
use App\Events\Category\CategoryRestored;
use App\Events\Category\CategoryUpdated;
use App\Exceptions\GeneralException;
use App\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CategoryService.
 */
class CategoryService extends BaseService
{
    /**
     * CategoryService Constructor.
     */
    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeCategory(array $data = []): Category
    {
        DB::beginTransaction();
        try {
            $categoryData = [
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $category = $this->model::create($categoryData);

            event(new CategoryCreated($category));

            DB::commit();

            return $category;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateCategory(Category $category, array $data = []): Category
    {
        DB::beginTransaction();

        try {
            $category->update([
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new CategoryUpdated($category));

            DB::commit();

            return $category;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyCategory($id): bool
    {
        DB::beginTransaction();

        try {
            $category = Category::findOrFail((int) $id);

            $category->is_active = false;
            $category->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($category) {
                $category->save();
            });

            $result = $category->delete();

            event(new CategoryDestroyed($category));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Category Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreCategory($id): bool
    {
        DB::beginTransaction();
        try {

            $category = Category::withTrashed()->findOrFail($id);

            $category->is_active = true;
            $category->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $category->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $category->restore();

            event(new CategoryRestored($category));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Category Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Category.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteCategory($id): bool
    {
        DB::beginTransaction();
        try {
            $category = Category::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($category) {
                $category->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('category')
                ->event('forceDeleted')
                ->performedOn($category)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new CategoryDeleted($category));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Category Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Category.'));
        }
    }
}
