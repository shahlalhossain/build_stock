<?php

namespace App\Http\Controllers;

use App\DataTables\NotificationTemplatesDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\NotificationTemplate\StoreNotificationTemplateRequest;
use App\Http\Requests\NotificationTemplate\UpdateNotificationTemplateRequest;
use App\Models\NotificationChannel;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Services\NotificationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class NotificationTemplatesController extends Controller
{
    protected NotificationTemplateService $notificationTemplateService;

    /**
     * Gets the service that does the real work.
     */
    public function __construct(NotificationTemplateService $notificationTemplateService)
    {
        $this->notificationTemplateService = $notificationTemplateService;
    }

    /**
     * Shows the list of all templates.
     */
    public function index(NotificationTemplatesDataTable $dataTable)
    {
        return $dataTable->render('notification-template.index');
    }

    /**
     * Shows the empty form to add a new template.
     */
    public function create(): View
    {
        $data['settings'] = NotificationSetting::query()->orderBy('name')->get();
        $data['channels'] = NotificationChannel::query()->active()->orderBy('name')->get();

        return view('notification-template.create', $data);
    }

    /**
     * Saves a new template and goes back to the list.
     */
    public function store(StoreNotificationTemplateRequest $request): RedirectResponse
    {
        try {
            $this->notificationTemplateService->storeTemplate($request->validated());

            return redirect()->route('notification-template.index')->with('success', 'New Notification Template Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Notification Template Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Notification Template: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Shows all details of one template.
     */
    public function show(NotificationTemplate $notificationTemplate): View
    {
        $data['notificationTemplate'] = $notificationTemplate->load(['setting', 'channel', 'creator', 'updater']);

        return view('notification-template.show', $data);
    }

    /**
     * Shows the form to change one template.
     */
    public function edit(NotificationTemplate $notificationTemplate): View
    {
        $data['notificationTemplate'] = $notificationTemplate->load(['setting', 'channel']);

        return view('notification-template.edit', $data);
    }

    /**
     * Saves the changes of a template and goes back to the list.
     */
    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $notificationTemplate): RedirectResponse
    {
        try {
            $this->notificationTemplateService->updateTemplate($notificationTemplate, $request->validated());

            return redirect()->route('notification-template.index')->with('success', 'Notification Template Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Notification Template Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Notification Template: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Switches a template on or off and answers with JSON.
     */
    public function toggleStatus(NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            $template = $this->notificationTemplateService->toggleTemplateStatus($notificationTemplate);
            $message = $template->is_active ? 'Notification Template Enabled Successfully.' : 'Notification Template Disabled Successfully.';

            return response()->json(['success' => true, 'message' => $message, 'is_active' => $template->is_active]);
        } catch (GeneralException $exception) {
            Log::error('Notification Template Status Change Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Changing Notification Template Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Changing the Template Status.'], 500);
        }
    }

    /**
     * Deletes a template for good and answers with JSON.
     */
    public function destroy(NotificationTemplate $notificationTemplate): JsonResponse
    {
        try {
            $this->notificationTemplateService->deleteTemplate($notificationTemplate);

            return response()->json(['success' => true, 'message' => 'Notification Template Deleted Successfully.']);
        } catch (GeneralException $exception) {
            Log::error('Notification Template Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Notification Template: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Notification Template.'], 500);
        }
    }
}
