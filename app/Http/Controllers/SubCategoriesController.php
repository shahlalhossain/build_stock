<?php

namespace App\Http\Controllers;

use App\DataTables\SubCategoriesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\SubCategory\StoreSubCategoryRequest;
use App\Http\Requests\SubCategory\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\SubCategoryService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SubCategoriesController extends Controller
{
    protected SubCategoryService $subCategoryService;

    public function __construct(SubCategoryService $subCategoryService)
    {
        $this->subCategoryService = $subCategoryService;
    }

    public function index(SubCategoriesDataTable $subCategoriesDataTable)
    {
        $subCategoriesDataTable->showTrashed = false;

        return $subCategoriesDataTable->render('sub_category.index');
    }

    public function create()
    {
        $data['categories'] = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('sub_category.create', $data);
    }

    public function store(StoreSubCategoryRequest $subCategoryRequest)
    {
        try {
            $this->subCategoryService->storeSubCategory($subCategoryRequest->validated());

            return redirect()->route('sub-category.index')->with('success', 'New Sub-Category Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Sub-Category Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Sub-Category: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(SubCategory $subCategory)
    {
        $data['subCategory'] = $subCategory->load(['category', 'creator', 'updater', 'deleter']);

        return view('sub_category.show', $data);
    }

    public function edit(SubCategory $subCategory): View
    {
        $data['subCategory'] = $subCategory;
        $data['categories'] = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('sub_category.edit', $data);
    }

    public function update(UpdateSubCategoryRequest $subCategoryRequest, SubCategory $subCategory): RedirectResponse
    {
        try {
            $this->subCategoryService->updateSubCategory($subCategory, $subCategoryRequest->validated());

            return redirect()->route('sub-category.index')->with('success', 'Sub-Category Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Sub-Category Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Sub-Category: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->subCategoryService->destroySubCategory($id);

            return response()->json(['success' => true, 'message' => 'Sub-Category Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Sub-Category Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Sub-Category Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Sub-Category Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Sub-Category: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Sub-Category.'], 500);
        }
    }

    public function trash(SubCategoriesDataTable $subCategoriesDataTable)
    {
        $subCategoriesDataTable->showTrashed = true;

        return $subCategoriesDataTable->render('sub_category.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->subCategoryService->restoreSubCategory($id);

            return response()->json(['success' => true, 'message' => 'Sub-Category Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Sub-Category Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Sub-Category Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Sub-Category Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Sub-Category: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Sub-Category.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->subCategoryService->deleteSubCategory($id);

            return response()->json(['success' => true, 'message' => 'Sub-Category Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Sub-Category.', 'error' => $exception->getMessage()], 500);
        }
    }
}
