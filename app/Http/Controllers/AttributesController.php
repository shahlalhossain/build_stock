<?php

namespace App\Http\Controllers;

use App\DataTables\AttributesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Attribute\StoreAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Services\AttributeService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AttributesController extends Controller
{
    protected AttributeService $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function index(AttributesDataTable $attributesDataTable)
    {
        $attributesDataTable->showTrashed = false;

        return $attributesDataTable->render('attribute.index');
    }

    public function create()
    {
        return view('attribute.create');
    }

    public function store(StoreAttributeRequest $attributeRequest)
    {
        try {
            $this->attributeService->storeAttribute($attributeRequest->validated());

            return redirect()->route('attribute.index')->with('success', 'New Attribute Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Attribute Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Attribute: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Attribute $attribute)
    {
        $data['attribute'] = $attribute->load(['values' => function ($query) {
            $query->orderBy('value');
        }, 'creator', 'updater', 'deleter']);

        return view('attribute.show', $data);
    }

    public function edit(Attribute $attribute): View
    {
        $data['attribute'] = $attribute->load(['values' => function ($query) {
            $query->orderBy('value');
        }]);

        return view('attribute.edit', $data);
    }

    public function update(UpdateAttributeRequest $attributeRequest, Attribute $attribute): RedirectResponse
    {
        try {
            $this->attributeService->updateAttribute($attribute, $attributeRequest->validated());

            return redirect()->route('attribute.index')->with('success', 'Attribute Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Attribute Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Attribute: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->attributeService->destroyAttribute($id);

            return response()->json(['success' => true, 'message' => 'Attribute Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Attribute Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Attribute Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Attribute Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Attribute: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Attribute.'], 500);
        }
    }

    public function trash(AttributesDataTable $attributesDataTable)
    {
        $attributesDataTable->showTrashed = true;

        return $attributesDataTable->render('attribute.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->attributeService->restoreAttribute($id);

            return response()->json(['success' => true, 'message' => 'Attribute Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Attribute Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Attribute Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Attribute Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Attribute: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Attribute.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->attributeService->deleteAttribute($id);

            return response()->json(['success' => true, 'message' => 'Attribute Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Attribute.', 'error' => $exception->getMessage()], 500);
        }
    }
}
