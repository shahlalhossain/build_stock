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
 * Contacts/Addresses/Payment Accounts/MFS Accounts are each owned by their
 * own dedicated service (delete-all-and-recreate on every store/update)
 * rather than being synced inline here.
 */
class SupplierService extends BaseService
{
    protected SupplierContactService $supplierContactService;

    protected SupplierAddressService $supplierAddressService;

    protected SupplierPaymentAccountService $supplierPaymentAccountService;

    protected SupplierMfsAccountService $supplierMfsAccountService;

    /**
     * SupplierService Constructor.
     */
    public function __construct(
        Supplier $supplier,
        SupplierContactService $supplierContactService,
        SupplierAddressService $supplierAddressService,
        SupplierPaymentAccountService $supplierPaymentAccountService,
        SupplierMfsAccountService $supplierMfsAccountService
    ) {
        $this->model = $supplier;
        $this->supplierContactService = $supplierContactService;
        $this->supplierAddressService = $supplierAddressService;
        $this->supplierPaymentAccountService = $supplierPaymentAccountService;
        $this->supplierMfsAccountService = $supplierMfsAccountService;
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
                'code' => $this->generateCode(),
                'name' => $data['name'] ?? null,
                'tin_number' => $data['tin_number'] ?? null,
                'bin_number' => $data['bin_number'] ?? null,
                'payment_terms_days' => $data['payment_terms_days'] ?? null,
                'credit_limit' => $data['credit_limit'] ?? null,
                'minimum_order_quantity' => $data['minimum_order_quantity'] ?? null,
                'minimum_order_amount' => $data['minimum_order_amount'] ?? null,
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $supplier = $this->model::create($supplierData);

            $this->supplierContactService->syncContacts($supplier, $data['contacts'] ?? []);
            $this->supplierAddressService->syncAddresses($supplier, $data['addresses'] ?? []);
            $this->supplierPaymentAccountService->syncPaymentAccounts($supplier, $data['payment_accounts'] ?? []);
            $this->supplierMfsAccountService->syncMfsAccounts($supplier, $data['mfs_accounts'] ?? []);

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
                'name' => $data['name'] ?? null,
                'tin_number' => $data['tin_number'] ?? null,
                'bin_number' => $data['bin_number'] ?? null,
                'payment_terms_days' => $data['payment_terms_days'] ?? null,
                'credit_limit' => $data['credit_limit'] ?? null,
                'minimum_order_quantity' => $data['minimum_order_quantity'] ?? null,
                'minimum_order_amount' => $data['minimum_order_amount'] ?? null,
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $this->supplierContactService->syncContacts($supplier, $data['contacts'] ?? []);
            $this->supplierAddressService->syncAddresses($supplier, $data['addresses'] ?? []);
            $this->supplierPaymentAccountService->syncPaymentAccounts($supplier, $data['payment_accounts'] ?? []);
            $this->supplierMfsAccountService->syncMfsAccounts($supplier, $data['mfs_accounts'] ?? []);

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
     * Generate the next Sequential Supplier Code (e.g. SUP-0001).
     */
    protected function generateCode(): string
    {
        $lastNumber = Supplier::withTrashed()
            ->where('code', 'like', 'SUP-%')
            ->selectRaw('MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_number')
            ->value('max_number');

        return 'SUP-'.str_pad((int) $lastNumber + 1, 4, '0', STR_PAD_LEFT);
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
