<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

/**
 * Class SupplierMfsAccountService.
 */
class SupplierMfsAccountService
{
    /**
     * Replace the supplier's MFS accounts with the submitted rows (delete-all-and-recreate).
     */
    public function syncMfsAccounts(Supplier $supplier, array $rows = []): void
    {
        $supplier->mfsAccounts()->delete();

        foreach ($rows as $row) {
            if (blank($row['mfs_account_number'] ?? null)) {
                continue;
            }

            $supplier->mfsAccounts()->create([
                'mfs_operator_name' => $row['mfs_operator_name'] ?? null,
                'mfs_account_number' => $row['mfs_account_number'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }
    }
}
