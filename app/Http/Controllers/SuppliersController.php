<?php

namespace App\Http\Controllers;

use App\DataTables\SuppliersDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\AddressType;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\GeoDivision;
use App\Models\MFSCompany;
use App\Models\Supplier;
use App\Models\SupplierType;
use App\Services\SupplierService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SuppliersController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(SuppliersDataTable $suppliersDataTable)
    {
        $suppliersDataTable->showTrashed = false;

        return $suppliersDataTable->render('supplier.index');
    }

    public function create()
    {
        $data['supplierTypes'] = SupplierType::select('id', 'name')->get();
        $data['addressTypes'] = AddressType::where('is_active', true)->select('id', 'name')->get();
        $data['divisions'] = GeoDivision::orderBy('name_en')->get(['id', 'name_en', 'name_bn']);
        $data['mfsCompanies'] = MFSCompany::where('status', 'Active')->select('id', 'service_name')->get();
        $data['banks'] = Bank::where('is_active', true)->orderBy('bank_name')->get(['id', 'bank_name']);

        return view('supplier.create', $data);
    }

    public function getBranchesByBank(Request $request): JsonResponse
    {
        $branches = BankBranch::where('bank_id', $request->bank_id)
            ->where('is_active', true)
            ->orderBy('branch_name')
            ->get(['id', 'branch_name']);

        return response()->json($branches);
    }

    public function store(StoreSupplierRequest $supplierRequest)
    {
        try {
            $this->supplierService->storeSupplier($supplierRequest->validated());

            return redirect()->route('supplier.index')->with('success', 'New Supplier Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Supplier Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Supplier: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Supplier $supplier)
    {
        $data['supplier'] = $supplier->load(['contacts', 'addresses', 'paymentAccounts', 'mfsAccounts', 'creator', 'updater', 'deleter']);

        return view('supplier.show', $data);
    }

    public function edit(Supplier $supplier): View
    {
        $data['supplier'] = $supplier->load(['contacts', 'addresses', 'paymentAccounts', 'mfsAccounts']);

        return view('supplier.edit', $data);
    }

    public function update(UpdateSupplierRequest $supplierRequest, Supplier $supplier): RedirectResponse
    {
        try {
            $this->supplierService->updateSupplier($supplier, $supplierRequest->validated());

            return redirect()->route('supplier.index')->with('success', 'Supplier Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Supplier Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Supplier: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->supplierService->destroySupplier($id);

            return response()->json(['success' => true, 'message' => 'Supplier Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Supplier Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Supplier Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Supplier Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Supplier: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Supplier.'], 500);
        }
    }

    public function trash(SuppliersDataTable $suppliersDataTable)
    {
        $suppliersDataTable->showTrashed = true;

        return $suppliersDataTable->render('supplier.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->supplierService->restoreSupplier($id);

            return response()->json(['success' => true, 'message' => 'Supplier Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Supplier Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Supplier Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Supplier Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Supplier: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Supplier.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->supplierService->deleteSupplier($id);

            return response()->json(['success' => true, 'message' => 'Supplier Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Supplier.', 'error' => $exception->getMessage()], 500);
        }
    }
}
