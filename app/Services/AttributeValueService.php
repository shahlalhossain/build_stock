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
     * Diff the given Attribute's Values against the Submitted set, by Value
     * String (unique per Attribute — see the attribute_values Migration) rather
     * than Delete-All-and-Recreate: an Unchanged Value keeps its own row and id,
     * only genuinely New Values are inserted, and only genuinely Removed Values
     * are deleted. This matters because attribute_values.id is referenced
     * elsewhere (e.g. product_attribute_values, product_variant_values) without
     * always being enforced by a Foreign Key — Recreating every row on every
     * Save silently orphaned those References in the past, and would also throw
     * an FK-Violation Error whenever a Value IS Foreign-Key-Referenced (e.g. by
     * an existing Product Variant).
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function syncValues(Attribute $attribute, array $values = []): void
    {
        try {
            $submittedValues = array_values(array_unique(array_filter(array_map('trim', $values))));

            $existingByValue = $attribute->values()->get()->keyBy('value');

            $valuesToDelete = $existingByValue->keys()->diff($submittedValues);
            if ($valuesToDelete->isNotEmpty()) {
                $attribute->values()->whereIn('value', $valuesToDelete)->delete();
            }

            $valuesToInsert = array_values(array_diff($submittedValues, $existingByValue->keys()->all()));
            if (! empty($valuesToInsert)) {
                $attribute->values()->insert(array_map(fn (string $value) => [
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ], $valuesToInsert));
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
