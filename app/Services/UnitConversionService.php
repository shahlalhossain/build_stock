<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\Product;
use App\Models\UnitConversion;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class UnitConversionService.
 */
class UnitConversionService extends BaseService
{
    /**
     * UnitConversionService Constructor.
     */
    public function __construct(UnitConversion $unitConversion)
    {
        $this->model = $unitConversion;
    }

    /**
     * Normalize a Quantity given in an arbitrary transaction Unit into the
     * Product's own base Unit (products.unit_id), the single Unit that
     * product_stocks.quantity is always denominated in.
     *
     * If the given Unit IS the Product's base Unit, the Factor is 1 — no
     * lookup needed. Otherwise a matching unit_conversions row is required;
     * there is no implicit 1:1 fallback, since silently assuming a Factor
     * would risk corrupting Stock Balances.
     *
     * @throws GeneralException
     */
    public function toBaseUnit(Product $product, ?int $unitId, float $quantity): float
    {
        if (! $unitId || (int) $unitId === (int) $product->unit_id) {
            return $quantity;
        }

        $conversion = UnitConversion::query()
            ->where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        if (! $conversion) {
            throw new GeneralException(__(
                'No Unit Conversion is Configured for :product from the Selected Unit to its Base Unit.',
                ['product' => $product->name]
            ));
        }

        return $quantity * (float) $conversion->factor_to_base;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeUnitConversion(array $data = []): UnitConversion
    {
        DB::beginTransaction();
        try {
            $unitConversion = $this->model::create([
                'product_id' => $data['product_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'factor_to_base' => $data['factor_to_base'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $unitConversion;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Unit Conversion.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateUnitConversion(UnitConversion $unitConversion, array $data = []): UnitConversion
    {
        DB::beginTransaction();
        try {
            $unitConversion->update([
                'product_id' => $data['product_id'] ?? $unitConversion->product_id,
                'unit_id' => $data['unit_id'] ?? $unitConversion->unit_id,
                'factor_to_base' => $data['factor_to_base'] ?? $unitConversion->factor_to_base,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $unitConversion;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Unit Conversion.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyUnitConversion($id): bool
    {
        DB::beginTransaction();
        try {
            $unitConversion = UnitConversion::findOrFail((int) $id);

            $unitConversion->is_active = false;
            $unitConversion->deleted_by = Auth::id();

            activity()->withoutLogs(function () use ($unitConversion) {
                $unitConversion->save();
            });

            $result = $unitConversion->delete();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Unit Conversion Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Unit Conversion.'));
        }
    }
}
