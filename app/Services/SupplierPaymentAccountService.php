<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

/**
 * Class SupplierPaymentAccountService.
 */
class SupplierPaymentAccountService
{
    /**
     * Replace the supplier's payment accounts with the submitted rows (delete-all-and-recreate).
     *
     * bank_name/branch_name are submitted as the selected option's text
     * (they're plain string columns, not foreign keys), and the routing
     * number is looked up server-side from that Bank+Branch pair rather
     * than trusting the form's disabled, display-only routing input.
     */
    public function syncPaymentAccounts(Supplier $supplier, array $rows = []): void
    {
        $supplier->paymentAccounts()->delete();

        foreach ($rows as $row) {
            if (blank($row['account_number'] ?? null)) {
                continue;
            }

            $supplier->paymentAccounts()->create([
                'account_name' => $row['account_name'] ?? null,
                'account_number' => $row['account_number'] ?? null,
                'bank_name' => $row['bank_name'] ?? null,
                'branch_name' => $row['branch_name'] ?? null,
                'routing_number' => $this->resolveRoutingNumber($row['bank_name'] ?? null, $row['branch_name'] ?? null),
                'remarks' => $row['remarks'] ?? null,
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Look up the Branch's routing number from the selected Bank + Branch name pair.
     */
    protected function resolveRoutingNumber(?string $bankName, ?string $branchName): ?string
    {
        if (blank($bankName) || blank($branchName)) {
            return null;
        }

        $bank = Bank::where('bank_name', $bankName)->first();

        return BankBranch::when($bank, fn ($query) => $query->where('bank_id', $bank->id))
            ->where('branch_name', $branchName)
            ->value('routing_no');
    }
}
