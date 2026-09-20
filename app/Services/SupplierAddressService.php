<?php

namespace App\Services;

use App\Models\GeoDistrict;
use App\Models\GeoDivision;
use App\Models\GeoThana;
use App\Models\Supplier;

/**
 * Class SupplierAddressService.
 */
class SupplierAddressService
{
    /**
     * Replace the supplier's addresses with the submitted rows (delete-all-and-recreate).
     *
     * Division/District/Thana are submitted as ids (cascading selects) and
     * resolved to their display names server-side, matching ProjectService's
     * saveAddress(), so each address row is self-contained and joinless to read.
     */
    public function syncAddresses(Supplier $supplier, array $rows = []): void
    {
        // addresses has no soft-delete columns (unlike contacts/payment/mfs accounts),
        // so this delete() is a hard delete, not the app's usual soft-delete.
        $supplier->addresses()->delete();

        foreach ($rows as $row) {
            $division = GeoDivision::find($row['division_id'] ?? null);
            $district = GeoDistrict::find($row['district_id'] ?? null);
            $thana = GeoThana::find($row['thana_id'] ?? null);

            $supplier->addresses()->create([
                'address_type_id' => $row['address_type'] ?? null,
                'address' => $row['address'] ?? null,
                'division_id' => $division?->id,
                'division_name' => $division?->name_en,
                'district_id' => $district?->id,
                'district_name' => $district?->name_en,
                'thana_id' => $thana?->id,
                'thana_name' => $thana?->name_en,
            ]);
        }
    }
}
