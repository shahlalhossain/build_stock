<?php

namespace App\Http\Controllers;

use App\DataTables\CategoriesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CategoriesController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(CategoriesDataTable $categoriesDataTable)
    {
        $categoriesDataTable->showTrashed = false;

        return $categoriesDataTable->render('category.index');
    }

    public function create()
    {
        return view('category.create');
    }

    public function store(StoreCategoryRequest $categoryRequest)
    {
        try {
            $this->categoryService->storeCategory($categoryRequest->validated());

            return redirect()->route('category.index')->with('success', 'New Category Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Category Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Category: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Category $category)
    {
        $data['category'] = $category->load(['creator', 'updater', 'deleter']);

        return view('category.show', $data);
    }

    public function edit(Category $category): View
    {
        $data['category'] = $category;

        return view('category.edit', $data);
    }

    public function update(UpdateCategoryRequest $categoryRequest, Category $category): RedirectResponse
    {
        try {
            $this->categoryService->updateCategory($category, $categoryRequest->validated());

            return redirect()->route('category.index')->with('success', 'Category Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Category Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Category: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->categoryService->destroyCategory($id);

            return response()->json(['success' => true, 'message' => 'Category Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Category Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Category Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Category Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Category: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Category.'], 500);
        }
    }

    public function trash(CategoriesDataTable $categoriesDataTable)
    {
        $categoriesDataTable->showTrashed = true;

        return $categoriesDataTable->render('category.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->categoryService->restoreCategory($id);

            return response()->json(['success' => true, 'message' => 'Category Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Category Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Category Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Category Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Category: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Category.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id);

            return response()->json(['success' => true, 'message' => 'Category Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Category.', 'error' => $exception->getMessage()], 500);
        }
    }
}
