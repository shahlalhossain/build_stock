<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\Attribute;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class AttributeValueService.
 */
class AttributeValueService extends BaseService
{
    /**
     * Delete-all-and-recreate the given Attribute's Values.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function syncValues(Attribute $attribute, array $values = []): void
    {
        try {
            $attribute->values()->delete();

            $rows = array_map(fn (string $value) => [
                'attribute_id' => $attribute->id,
                'value' => $value,
            ], array_filter(array_map('trim', $values)));

            if (! empty($rows)) {
                $attribute->values()->insert($rows);
            }
        } catch (Throwable $exception) {
            Log::error('Attribute Values Sync Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Saving the Attribute Values.'));
        }
    }

    /**
     * Hard-delete all Values belonging to the given Attribute.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteAllForAttribute(Attribute $attribute): void
    {
        try {
            $attribute->values()->delete();
        } catch (Throwable $exception) {
            Log::error('Attribute Values Deletion Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Deleting the Attribute Values.'));
        }
    }
}
