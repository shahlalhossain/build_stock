<?php

namespace App\Http\Controllers;

use App\DataTables\NotificationChannelsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\NotificationChannel\StoreNotificationChannelRequest;
use App\Http\Requests\NotificationChannel\UpdateNotificationChannelRequest;
use App\Models\NotificationChannel;
use App\Services\NotificationChannelService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class NotificationChannelsController extends Controller
{
    protected NotificationChannelService $channelService;

    /**
     * Sets up the controller with the channel service.
     */
    public function __construct(NotificationChannelService $channelService)
    {
        $this->channelService = $channelService;
    }

    /**
     * Shows the list of channels.
     */
    public function index(NotificationChannelsDataTable $dataTable)
    {
        $dataTable->showTrashed = false;

        return $dataTable->render('notification-channel.index');
    }

    /**
     * Shows the form to create a channel.
     */
    public function create(): View
    {
        return view('notification-channel.create');
    }

    /**
     * Saves a new channel and goes back to the list.
     */
    public function store(StoreNotificationChannelRequest $request): RedirectResponse
    {
        try {
            $this->channelService->storeChannel($request->validated());

            return redirect()->route('notification-channel.index')->with('success', 'New Notification Channel Created Successfully.');
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Creation Failed: '.$exception->getMessage());

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Notification Channel: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Shows the full details of one channel.
     */
    public function show(NotificationChannel $notificationChannel): View
    {
        $notificationChannel->load(['creator', 'updater', 'deleter']);
        $notificationChannel->loadCount(['templates', 'settings']);

        $data['channel'] = $notificationChannel;

        return view('notification-channel.show', $data);
    }

    /**
     * Shows the form to edit a channel.
     */
    public function edit(NotificationChannel $notificationChannel): View
    {
        $data['channel'] = $notificationChannel;

        return view('notification-channel.edit', $data);
    }

    /**
     * Saves the changes made to a channel and goes back to the list.
     */
    public function update(UpdateNotificationChannelRequest $request, NotificationChannel $notificationChannel): RedirectResponse
    {
        try {
            $this->channelService->updateChannel($notificationChannel, $request->validated());

            return redirect()->route('notification-channel.index')->with('success', 'Notification Channel Updated Successfully.');
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Update Failed: '.$exception->getMessage());

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Notification Channel: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    /**
     * Switches a channel on or off and replies with JSON.
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $channel = $this->channelService->toggleChannelStatus($id);
            $label = $channel->is_active ? 'Enabled' : 'Disabled';

            return response()->json(['success' => true, 'message' => "Notification Channel {$label} Successfully."]);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Channel Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Channel Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Status Change Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Changing Notification Channel Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Changing the Channel Status.'], 500);
        }
    }

    /**
     * Moves a channel to the trash and replies with JSON.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->channelService->destroyChannel($id);

            return response()->json(['success' => true, 'message' => 'Notification Channel Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Channel Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Channel Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Notification Channel: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Notification Channel.'], 500);
        }
    }

    /**
     * Shows the list of channels in the trash.
     */
    public function trash(NotificationChannelsDataTable $dataTable)
    {
        $dataTable->showTrashed = true;

        return $dataTable->render('notification-channel.trashed');
    }

    /**
     * Brings a channel back from the trash and replies with JSON.
     */
    public function restore($id): JsonResponse
    {
        try {
            $this->channelService->restoreChannel($id);

            return response()->json(['success' => true, 'message' => 'Notification Channel Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Channel Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Channel Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Notification Channel: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Notification Channel.'], 500);
        }
    }

    /**
     * Deletes a channel for good and replies with JSON.
     */
    public function delete($id): JsonResponse
    {
        try {
            $this->channelService->deleteChannel($id);

            return response()->json(['success' => true, 'message' => 'Notification Channel Deleted Permanently.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Notification Channel Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Notification Channel Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Notification Channel Permanent Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Notification Channel: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Notification Channel.'], 500);
        }
    }
}
