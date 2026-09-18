<?php

namespace App\Services;

use App\Events\Attribute\AttributeCreated;
use App\Events\Attribute\AttributeDeleted;
use App\Events\Attribute\AttributeDestroyed;
use App\Events\Attribute\AttributeRestored;
use App\Events\Attribute\AttributeUpdated;
use App\Exceptions\GeneralException;
use App\Models\Attribute;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class AttributeService.
 */
class AttributeService extends BaseService
{
    protected AttributeValueService $attributeValueService;

    /**
     * AttributeService Constructor.
     */
    public function __construct(Attribute $attribute, AttributeValueService $attributeValueService)
    {
        $this->model = $attribute;
        $this->attributeValueService = $attributeValueService;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeAttribute(array $data = []): Attribute
    {
        DB::beginTransaction();
        try {
            $attributeData = [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $attribute = $this->model::create($attributeData);

            $this->attributeValueService->syncValues($attribute, $data['values'] ?? []);

            event(new AttributeCreated($attribute));

            DB::commit();

            return $attribute;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Attribute.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateAttribute(Attribute $attribute, array $data = []): Attribute
    {
        DB::beginTransaction();

        try {
            $attribute->update([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $this->attributeValueService->syncValues($attribute, $data['values'] ?? []);

            event(new AttributeUpdated($attribute));

            DB::commit();

            return $attribute;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Attribute.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyAttribute($id): bool
    {
        DB::beginTransaction();

        try {
            $attribute = Attribute::findOrFail((int) $id);

            $attribute->is_active = false;
            $attribute->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($attribute) {
                $attribute->save();
            });

            $result = $attribute->delete();

            event(new AttributeDestroyed($attribute));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Attribute Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Attribute.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreAttribute($id): bool
    {
        DB::beginTransaction();
        try {

            $attribute = Attribute::withTrashed()->findOrFail($id);

            $attribute->is_active = true;
            $attribute->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $attribute->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $attribute->restore();

            event(new AttributeRestored($attribute));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Attribute Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Attribute.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteAttribute($id): bool
    {
        DB::beginTransaction();
        try {
            $attribute = Attribute::withTrashed()->findOrFail($id);

            $this->attributeValueService->deleteAllForAttribute($attribute);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($attribute) {
                $attribute->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('attribute')
                ->event('forceDeleted')
                ->performedOn($attribute)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new AttributeDeleted($attribute));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Attribute Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Attribute.'));
        }
    }
}
