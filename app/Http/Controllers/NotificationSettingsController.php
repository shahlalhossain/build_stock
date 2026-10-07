<?php

namespace App\Http\Controllers;

use App\DataTables\NotificationSettingsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\NotificationSetting\StoreNotificationSettingRequest;
use App\Http\Requests\NotificationSetting\UpdateNotificationSettingRequest;
use App\Models\NotificationChannel;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationSettingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class NotificationSettingsController extends Controller
{
    protected NotificationSettingService $notificationSettingService;

    /**
     * Gets the service that does the real work.
     */
    public function __construct(NotificationSettingService $notificationSettingService)
    {
        $this->notificationSettingService = $notificationSettingService;
    }

    /**
     * Shows the list of settings.
     */
    public function index(NotificationSettingsDataTable $notificationSettingsDataTable)
    {
        $notificationSettingsDataTable->showTrashed = false;

        return $notificationSettingsDataTable->render('notification-setting.index');
    }

    /**
     * Shows the empty form for a new setting.
     */
    public function create(): View
    {
        $data = $this->formOptions();
        $data['selectedChannelIds'] = [];
        $data['selectedUserIds'] = [];
        $data['selectedRoleNames'] = [];
        $data['selectedPermissionNames'] = [];

        return view('notification-setting.create', $data);
    }

    /**
     * Saves a new setting.
     */
    public function store(StoreNotificationSettingRequest $notificationSettingRequest): RedirectResponse
    {
        try {
            $this->notificationSettingService->storeSetting($notificationSettingRequest->validated());

            return redirect()->route('notification-setting.index')->with('success', 'New Notification Setting Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Notification Setting Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Notification Setting: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Shows the full details of one setting.
     */
    public function show(NotificationSetting $notificationSetting): View
    {
        $data['notificationSetting'] = $notificationSetting->load(['channels', 'templates', 'receiverRules', 'creator', 'updater', 'deleter']);
        $data['templatesByChannel'] = $notificationSetting->templates->keyBy('channel_id');
        $data['userNames'] = $this->userNamesForRules($notificationSetting);

        return view('notification-setting.show', $data);
    }

    /**
     * Shows the form to change a setting, filled with its current values.
     */
    public function edit(NotificationSetting $notificationSetting): View
    {
        $data = $this->formOptions();
        $data['notificationSetting'] = $notificationSetting;
        $data['selectedChannelIds'] = $this->activeChannelIds($notificationSetting);
        $data['selectedUserIds'] = $this->ruleValues($notificationSetting, NotificationSettingReceiver::TYPE_USER);
        $data['selectedRoleNames'] = $this->ruleValues($notificationSetting, NotificationSettingReceiver::TYPE_ROLE);
        $data['selectedPermissionNames'] = $this->ruleValues($notificationSetting, NotificationSettingReceiver::TYPE_PERMISSION);

        return view('notification-setting.edit', $data);
    }

    /**
     * Saves the changes made to a setting.
     */
    public function update(UpdateNotificationSettingRequest $notificationSettingRequest, NotificationSetting $notificationSetting): RedirectResponse
    {
        try {
            $this->notificationSettingService->updateSetting($notificationSetting, $notificationSettingRequest->validated());

            return redirect()->route('notification-setting.index')->with('success', 'Notification Setting Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Notification Setting Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Notification Setting: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Switches a setting on or off.
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $setting = $this->notificationSettingService->toggleSettingStatus($id);
            $statusText = $setting->is_active ? 'Enabled' : 'Disabled';

            return response()->json(['success' => true, 'message' => "Notification Setting {$statusText} Successfully.", 'is_active' => $setting->is_active]);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Setting Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Setting Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Setting Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Notification Setting Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Notification Setting Status.'], 500);
        }
    }

    /**
     * Moves a setting to the trash box.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->notificationSettingService->destroySetting($id);

            return response()->json(['success' => true, 'message' => 'Notification Setting Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Setting Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Setting Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Setting Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Notification Setting: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Notification Setting.'], 500);
        }
    }

    /**
     * Shows the settings that are in the trash box.
     */
    public function trash(NotificationSettingsDataTable $notificationSettingsDataTable)
    {
        $notificationSettingsDataTable->showTrashed = true;

        return $notificationSettingsDataTable->render('notification-setting.trashed');
    }

    /**
     * Brings a setting back from the trash box.
     */
    public function restore($id): JsonResponse
    {
        try {
            $this->notificationSettingService->restoreSetting($id);

            return response()->json(['success' => true, 'message' => 'Notification Setting Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Setting Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Setting Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Setting Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Notification Setting: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Notification Setting.'], 500);
        }
    }

    /**
     * Removes a setting permanently.
     */
    public function delete($id): JsonResponse
    {
        try {
            $this->notificationSettingService->deleteSetting($id);

            return response()->json(['success' => true, 'message' => 'Notification Setting Deleted Permanently.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Setting Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Setting Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Setting Permanent Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Notification Setting Permanently: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Notification Setting.'], 500);
        }
    }

    /**
     * Gets the lists used by the form: channels, users, roles and permissions.
     */
    private function formOptions(): array
    {
        return [
            'channels' => NotificationChannel::query()->active()->ordered()->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Gets the ids of the channels that are switched on for a setting.
     */
    private function activeChannelIds(NotificationSetting $setting): array
    {
        return $setting->channels()
            ->wherePivot('is_active', true)
            ->pluck('notification_channels.id')
            ->all();
    }

    /**
     * Gets the saved receiver values of one type (user, role or permission).
     */
    private function ruleValues(NotificationSetting $setting, string $type): array
    {
        return $setting->receiverRules()
            ->where('receiver_type', $type)
            ->pluck('receiver_value')
            ->all();
    }

    /**
     * Finds the names of the users used in the receiver rules, keyed by user id.
     */
    private function userNamesForRules(NotificationSetting $setting): array
    {
        $userIds = $this->ruleValues($setting, NotificationSettingReceiver::TYPE_USER);

        return User::query()->whereIn('id', $userIds)->pluck('name', 'id')->all();
    }
}
