<?php

namespace App\Http\Controllers;

use App\DataTables\FAQsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Faq\FAQStoreRequest;
use App\Http\Requests\Faq\FAQUpdateRequest;
use App\Models\FAQ;
use App\Models\FAQCategory;
use App\Services\FAQService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Exception;

class FAQsController extends Controller
{
    protected FAQService $faqService;

    /**
     * @param FAQService $faqService
     */
    public function __construct(FAQService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * @param FAQsDataTable $faqsDataTable
     * @return mixed
     */
    public function index(FAQsDataTable $faqsDataTable)
    {
        return $faqsDataTable->render('faq.index');
    }

    public function create() : View
    {
        $data['categories'] = FAQCategory::select('id', 'name')->get();
        return view('faq.create', $data);
    }

    public function store(FAQStoreRequest $faqStoreRequest)
    {
        try {
            $this->faqService->createFAQ($faqStoreRequest->validated());
            return redirect()->route('faq.index')->with('success', 'FAQ Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('FAQ Creation Failed: ' . $generalException->getMessage());
            return redirect()->route('faq.create')->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating FAQ: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show($id) : View
    {
        $data['faq'] = FAQ::withTrashed()->findOrFail($id);
        return view('faq.show', $data);
    }

    public function edit($id) : View
    {
        $data['categories'] = FAQCategory::select('id', 'name')->get();
        $data['faq']        = FAQ::findOrFail($id);

        return view('faq.edit', $data);
    }

    public function update(FAQUpdateRequest $faqRequest, FAQ $faq)
    {
        try {
            $this->faqService->update($faq, $faqRequest->validated());
            return redirect()->route('faq.index')->with('success', 'FAQ Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('FAQ Update Failed: ' . $generalException->getMessage());
            return redirect()->route('faq.edit')->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating FAQ: ' . $exception->getMessage());
            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function destroy($id) : JsonResponse
    {
        try {
            $this->faqService->destroyFAQ($id);
            return response()->json(['success' => true, 'message' => 'FAQ Deleted Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('FAQ Not Found' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'FAQ Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('FAQ Deletion Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on FAQ: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the FAQ.'], 500);
        }
    }

    public function trash(FAQsDataTable $faqsDataTable)
    {
        $faqsDataTable->showTrashed = true;
        return $faqsDataTable->render('faq.trashed');
    }

    public function restore($id) : JsonResponse
    {
        try {
            $this->faqService->restoreFAQ($id);
            return response()->json(['success' => true, 'message' => 'FAQ Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('FAQ Not Found: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'FAQ Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('FAQ Restoration Failed: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring FAQ: ' . $exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the FAQ.'], 500);
        }
    }

    public function delete($id) : JsonResponse
    {
        try {
            $this->faqService->deleteFAQ($id);
            return response()->json(['success' => true, 'message' => 'FAQ Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to Delete FAQ.', 'error' => $exception->getMessage()], 500);
        }
    }
}
