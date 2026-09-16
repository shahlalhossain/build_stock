<?php

namespace App\Services;

use App\Events\Supplier\SupplierCreated;
use App\Events\Supplier\SupplierDeleted;
use App\Events\Supplier\SupplierDestroyed;
use App\Events\Supplier\SupplierRestored;
use App\Events\Supplier\SupplierUpdated;
use App\Exceptions\GeneralException;
use App\Models\Supplier;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class SupplierService.
 *
 * Contacts/Addresses/Payment Accounts/MFS Accounts are always replaced wholesale
 * on store/update (delete existing, insert submitted rows) rather than diffed by id.
 */
class SupplierService extends BaseService
{
    /**
     * SupplierService Constructor.
     */
    public function __construct(Supplier $supplier)
    {
        $this->model = $supplier;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeSupplier(array $data = []): Supplier
    {
        DB::beginTransaction();
        try {
            $supplierData = [
                'supplier_type_id' => $data['supplier_type_id'] ?? null,
                'code' => $data['code'] ?? null,
                'name' => $data['name'] ?? null,
                'tin_number' => $data['tin_number'] ?? null,
                'bin_number' => $data['bin_number'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $supplier = $this->model::create($supplierData);

            $this->syncContacts($supplier, $data['contacts'] ?? []);
            $this->syncAddresses($supplier, $data['addresses'] ?? []);
            $this->syncPaymentAccounts($supplier, $data['payment_accounts'] ?? []);
            $this->syncMfsAccounts($supplier, $data['mfs_accounts'] ?? []);

            event(new SupplierCreated($supplier));

            DB::commit();

            return $supplier;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Supplier.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateSupplier(Supplier $supplier, array $data = []): Supplier
    {
        DB::beginTransaction();

        try {
            $supplier->update([
                'supplier_type_id' => $data['supplier_type_id'] ?? null,
                'code' => $data['code'] ?? null,
                'name' => $data['name'] ?? null,
                'tin_number' => $data['tin_number'] ?? null,
                'bin_number' => $data['bin_number'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $this->syncContacts($supplier, $data['contacts'] ?? []);
            $this->syncAddresses($supplier, $data['addresses'] ?? []);
            $this->syncPaymentAccounts($supplier, $data['payment_accounts'] ?? []);
            $this->syncMfsAccounts($supplier, $data['mfs_accounts'] ?? []);

            event(new SupplierUpdated($supplier));

            DB::commit();

            return $supplier;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Supplier.'));
        }
    }

    /**
     * Replace the supplier's contacts with the submitted rows.
     */
    protected function syncContacts(Supplier $supplier, array $rows): void
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

    /**
     * Replace the supplier's addresses with the submitted rows.
     */
    protected function syncAddresses(Supplier $supplier, array $rows): void
    {
        // addresses has no soft-delete columns (unlike contacts/payment/mfs accounts),
        // so this delete() is a hard delete, not the app's usual soft-delete.
        $supplier->addresses()->delete();

        foreach ($rows as $row) {
            $supplier->addresses()->create([
                'address_type' => $row['address_type'] ?? null,
                'address' => $row['address'] ?? null,
                'address_bn' => $row['address_bn'] ?? null,
                'division_name' => $row['division_name'] ?? null,
                'district_name' => $row['district_name'] ?? null,
                'thana_name' => $row['thana_name'] ?? null,
            ]);
        }
    }

    /**
     * Replace the supplier's payment accounts with the submitted rows.
     */
    protected function syncPaymentAccounts(Supplier $supplier, array $rows): void
    {
        $supplier->paymentAccounts()->delete();

        foreach ($rows as $row) {
            if (blank($row['account_number'] ?? null)) {
                continue;
            }

            $supplier->paymentAccounts()->create([
                'payment_method' => $row['payment_method'] ?? null,
                'account_name' => $row['account_name'] ?? null,
                'account_number' => $row['account_number'] ?? null,
                'bank_name' => $row['bank_name'] ?? null,
                'branch_name' => $row['branch_name'] ?? null,
                'routing_number' => $row['routing_number'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Replace the supplier's MFS accounts with the submitted rows.
     */
    protected function syncMfsAccounts(Supplier $supplier, array $rows): void
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

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroySupplier($id): bool
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::findOrFail((int) $id);

            $supplier->is_active = false;
            $supplier->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($supplier) {
                $supplier->save();
            });

            $result = $supplier->delete();

            event(new SupplierDestroyed($supplier));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Supplier Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Supplier.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreSupplier($id): bool
    {
        DB::beginTransaction();
        try {

            $supplier = Supplier::withTrashed()->findOrFail($id);

            $supplier->is_active = true;
            $supplier->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $supplier->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $supplier->restore();

            event(new SupplierRestored($supplier));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Supplier Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Supplier.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteSupplier($id): bool
    {
        DB::beginTransaction();
        try {
            $supplier = Supplier::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($supplier) {
                $supplier->contacts()->forceDelete();
                $supplier->addresses()->forceDelete();
                $supplier->paymentAccounts()->forceDelete();
                $supplier->mfsAccounts()->forceDelete();
                $supplier->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('supplier')
                ->event('forceDeleted')
                ->performedOn($supplier)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new SupplierDeleted($supplier));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Supplier Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Supplier.'));
        }
    }
}
