<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class NotificationChannelService.
 */
class NotificationChannelService extends BaseService
{
    /**
     * Sets up the service with the channel model.
     */
    public function __construct(NotificationChannel $notificationChannel)
    {
        $this->model = $notificationChannel;
    }

    /**
     * Saves a new channel (switched on) and returns it.
     *
     * @throws GeneralException
     */
    public function storeChannel(array $data = []): NotificationChannel
    {
        DB::beginTransaction();
        try {
            $channel = $this->model::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'driver' => $data['driver'],
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $channel;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Notification Channel Create Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Creating New Notification Channel.'));
        }
    }

    /**
     * Updates a channel's details and returns it. The code is never changed.
     *
     * @throws GeneralException
     */
    public function updateChannel(NotificationChannel $channel, array $data = []): NotificationChannel
    {
        DB::beginTransaction();
        try {
            $channel->update([
                'name' => $data['name'],
                'driver' => $data['driver'],
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $channel;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Notification Channel Update Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Updating the Notification Channel.'));
        }
    }

    /**
     * Switches a channel on if it is off, or off if it is on, and returns it.
     *
     * @throws GeneralException
     */
    public function toggleChannelStatus($id): NotificationChannel
    {
        DB::beginTransaction();
        try {
            $channel = $this->model::findOrFail((int) $id);

            $channel->update([
                'is_active' => ! $channel->is_active,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $channel;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Channel Toggle Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Changing the Channel Status.'));
        }
    }

    /**
     * Moves a channel to the trash. Not allowed while a notification setting still uses it.
     *
     * @throws GeneralException
     */
    public function destroyChannel($id): bool
    {
        DB::beginTransaction();
        try {
            $channel = $this->model::findOrFail((int) $id);

            if ($channel->settings()->exists()) {
                throw new GeneralException(__('Remove this channel from all notification settings before deleting it.'));
            }

            $channel->is_active = false;
            $channel->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($channel) {
                $channel->save();
            });

            $result = $channel->delete();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (GeneralException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Channel Destroy Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Notification Channel.'));
        }
    }

    /**
     * Brings a channel back from the trash.
     *
     * @throws GeneralException
     */
    public function restoreChannel($id): bool
    {
        DB::beginTransaction();
        try {
            $channel = $this->model::withTrashed()->findOrFail($id);

            $channel->is_active = true;
            $channel->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $channel->saveQuietly();

            $result = $channel->restore();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Channel Restore Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Notification Channel.'));
        }
    }

    /**
     * Deletes a channel for good. Not allowed if it has delivery history.
     *
     * @throws GeneralException
     */
    public function deleteChannel($id): bool
    {
        DB::beginTransaction();
        try {
            $channel = $this->model::withTrashed()->findOrFail($id);

            if ($channel->receivers()->exists()) {
                throw new GeneralException(__('This channel has delivery history and cannot be deleted permanently.'));
            }

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($channel) {
                $channel->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('notification_channel')
                ->event('forceDeleted')
                ->performedOn($channel)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (GeneralException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Channel Permanent Deletion Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Notification Channel.'));
        }
    }
}
