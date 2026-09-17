<?php

namespace App\Http\Controllers;

use App\DataTables\StoresDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Project;
use App\Models\Store;
use App\Services\StoreService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class StoresController extends Controller
{
    protected StoreService $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function index(StoresDataTable $storesDataTable)
    {
        $storesDataTable->showTrashed = false;

        return $storesDataTable->render('store.index');
    }

    public function create()
    {
        $data['projects'] = Project::query()->where('is_active', true)->orderBy('name')->get();

        return view('store.create', $data);
    }

    public function store(StoreStoreRequest $storeRequest)
    {
        try {
            $this->storeService->storeStore($storeRequest->validated());

            return redirect()->route('store.index')->with('success', 'New Store Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Store Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Store: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Store $store)
    {
        $data['store'] = $store->load(['project', 'creator', 'updater', 'deleter']);

        return view('store.show', $data);
    }

    public function edit(Store $store): View
    {
        $data['store'] = $store;
        $data['projects'] = Project::query()->where('is_active', true)->orderBy('name')->get();

        return view('store.edit', $data);
    }

    public function update(UpdateStoreRequest $storeRequest, Store $store): RedirectResponse
    {
        try {
            $this->storeService->updateStore($store, $storeRequest->validated());

            return redirect()->route('store.index')->with('success', 'Store Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Store Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Store: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->storeService->destroyStore($id);

            return response()->json(['success' => true, 'message' => 'Store Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Store Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Store Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Store Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Store: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Store.'], 500);
        }
    }

    public function trash(StoresDataTable $storesDataTable)
    {
        $storesDataTable->showTrashed = true;

        return $storesDataTable->render('store.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->storeService->restoreStore($id);

            return response()->json(['success' => true, 'message' => 'Store Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Store Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Store Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Store Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Store: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Store.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->storeService->deleteStore($id);

            return response()->json(['success' => true, 'message' => 'Store Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Store.', 'error' => $exception->getMessage()], 500);
        }
    }
}
