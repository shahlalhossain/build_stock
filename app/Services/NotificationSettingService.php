<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class NotificationSettingService.
 */
class NotificationSettingService extends BaseService
{
    /**
     * NotificationSettingService Constructor.
     */
    public function __construct(NotificationSetting $notificationSetting)
    {
        $this->model = $notificationSetting;
    }

    /**
     * Creates a new setting together with its channels and receivers.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeSetting(array $data = []): NotificationSetting
    {
        DB::beginTransaction();

        try {
            $setting = $this->model::create([
                'name' => $data['name'],
                'event_code' => $data['event_code'],
                'permission_name' => $data['permission_name'] ?? null,
                'permission_code' => $data['permission_code'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->syncChannels($setting, $data['channels'] ?? []);
            $this->syncReceiverRules($setting, $data);

            DB::commit();

            return $setting;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Setting Store Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Creating New Notification Setting.'));
        }
    }

    /**
     * Updates a setting and replaces its channels and receivers.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateSetting(NotificationSetting $setting, array $data = []): NotificationSetting
    {
        DB::beginTransaction();

        try {
            $setting->update([
                'name' => $data['name'],
                'event_code' => $data['event_code'],
                'permission_name' => $data['permission_name'] ?? null,
                'permission_code' => $data['permission_code'] ?? null,
                'description' => $data['description'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $this->syncChannels($setting, $data['channels'] ?? []);
            $this->syncReceiverRules($setting, $data);

            DB::commit();

            return $setting;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Setting Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Updating the Notification Setting.'));
        }
    }

    /**
     * Switches a setting on if it is off, and off if it is on.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function toggleSettingStatus($id): NotificationSetting
    {
        DB::beginTransaction();

        try {
            $setting = $this->model::findOrFail((int) $id);

            $setting->update([
                'is_active' => ! $setting->is_active,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $setting;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Setting Toggle Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Changing the Notification Setting Status.'));
        }
    }

    /**
     * Moves a setting to the trash box.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroySetting($id): bool
    {
        DB::beginTransaction();

        try {
            $setting = $this->model::findOrFail((int) $id);

            $setting->is_active = false;
            $setting->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($setting) {
                $setting->save();
            });

            $result = $setting->delete();

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Setting Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Notification Setting.'));
        }
    }

    /**
     * Brings a setting back from the trash box, unless its event code is already taken.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreSetting($id): bool
    {
        DB::beginTransaction();

        try {
            $setting = $this->model::withTrashed()->findOrFail((int) $id);

            $this->ensureEventCodeIsFree($setting);

            $setting->is_active = true;
            $setting->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $setting->saveQuietly();

            $result = $setting->restore();

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
            Log::error('Notification Setting Restore Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Notification Setting.'));
        }
    }

    /**
     * Removes a setting for good, unless notifications were already sent from it.
     *
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteSetting($id): bool
    {
        DB::beginTransaction();

        try {
            $setting = $this->model::withTrashed()->findOrFail((int) $id);

            if ($setting->messages()->exists()) {
                throw new GeneralException(__('This setting has notification history and cannot be deleted permanently.'));
            }

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($setting) {
                $setting->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('notification_setting')
                ->event('forceDeleted')
                ->performedOn($setting)
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
            Log::error('Notification Setting Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Notification Setting.'));
        }
    }

    /**
     * Saves the list of channels chosen for this setting. An empty list removes all of them.
     */
    private function syncChannels(NotificationSetting $setting, array $channelIds): void
    {
        $channelsToSync = [];

        foreach ($channelIds as $channelId) {
            $channelsToSync[(int) $channelId] = ['is_active' => true];
        }

        $setting->channels()->sync($channelsToSync);
    }

    /**
     * Replaces the old "who receives it" rules with the new users, roles and permissions.
     */
    private function syncReceiverRules(NotificationSetting $setting, array $data): void
    {
        $setting->receiverRules()->delete();

        $this->addReceiverRules($setting, NotificationSettingReceiver::TYPE_USER, $data['receiver_users'] ?? []);
        $this->addReceiverRules($setting, NotificationSettingReceiver::TYPE_ROLE, $data['receiver_roles'] ?? []);
        $this->addReceiverRules($setting, NotificationSettingReceiver::TYPE_PERMISSION, $data['receiver_permissions'] ?? []);
    }

    /**
     * Adds one rule for each chosen value of a single type (user, role or permission).
     */
    private function addReceiverRules(NotificationSetting $setting, string $type, array $values): void
    {
        foreach (array_unique($values) as $value) {
            $setting->receiverRules()->create([
                'receiver_type' => $type,
                'receiver_value' => (string) $value,
            ]);
        }
    }

    /**
     * Stops the restore if another live setting already uses the same event code.
     *
     * @throws GeneralException
     */
    private function ensureEventCodeIsFree(NotificationSetting $setting): void
    {
        $isTaken = $this->model::query()
            ->where('event_code', $setting->event_code)
            ->where('id', '!=', $setting->id)
            ->exists();

        if ($isTaken) {
            throw new GeneralException(__('Another setting already uses this event code, so this one cannot be restored.'));
        }
    }
}
