<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

/**
 * Class SupplierContactService.
 */
class SupplierContactService
{
    /**
     * Replace the supplier's contacts with the submitted rows (delete-all-and-recreate).
     */
    public function syncContacts(Supplier $supplier, array $rows = []): void
    {
        $supplier->contacts()->delete();

        foreach ($rows as $row) {
            $supplier->contacts()->create([
                'name' => $row['name'] ?? null,
                'designation' => $row['designation'] ?? null,
                'email' => $row['email'] ?? null,
                'mobile' => $row['mobile'] ?? null,
                'contact_type' => $row['contact_type'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }
    }
}
