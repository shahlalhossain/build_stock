<?php

namespace App\Services;

use App\Exceptions\GeneralException;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class NotificationTemplateService.
 */
class NotificationTemplateService extends BaseService
{
    /**
     * NotificationTemplateService Constructor.
     */
    public function __construct(NotificationTemplate $notificationTemplate)
    {
        $this->model = $notificationTemplate;
    }

    /**
     * Saves a new template.
     *
     * @throws GeneralException
     */
    public function storeTemplate(array $data = []): NotificationTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->model::create([
                'notification_setting_id' => $data['notification_setting_id'],
                'channel_id' => $data['channel_id'],
                'subject' => $data['subject'] ?? null,
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'variables' => self::parseVariables($data['variables'] ?? null),
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $template;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Template Store Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Creating New Notification Template.'));
        }
    }

    /**
     * Changes the text, variables and active flag of a template. Setting and channel never change.
     *
     * @throws GeneralException
     */
    public function updateTemplate(NotificationTemplate $template, array $data = []): NotificationTemplate
    {
        DB::beginTransaction();
        try {
            $template->update([
                'subject' => $data['subject'] ?? null,
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'variables' => self::parseVariables($data['variables'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? $template->is_active),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $template;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Template Update Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Updating the Notification Template.'));
        }
    }

    /**
     * Switches a template on if it is off, and off if it is on.
     *
     * @throws GeneralException
     */
    public function toggleTemplateStatus(NotificationTemplate $template): NotificationTemplate
    {
        DB::beginTransaction();
        try {
            $template->update([
                'is_active' => ! $template->is_active,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return $template;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Template Toggle Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was a Problem on Changing the Template Status.'));
        }
    }

    /**
     * Removes a template for good (there is no trash for templates).
     *
     * @throws GeneralException
     */
    public function deleteTemplate(NotificationTemplate $template): bool
    {
        DB::beginTransaction();
        try {
            $template->delete();

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Notification Template Delete Failed in Service: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Notification Template.'));
        }
    }

    /**
     * Turns the typed variables (one per line or comma separated) into a plain list of raw names.
     * Example: "brand_name, user_name" becomes ['brand_name', 'user_name'].
     */
    public static function splitVariableNames(?string $text): array
    {
        $names = preg_split('/[\s,]+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($names));
    }

    /**
     * Same as splitVariableNames, but keeps only the valid names (small letters, digits, underscore).
     */
    public static function parseVariables(?string $text): array
    {
        $valid = array_filter(self::splitVariableNames($text), fn (string $name) => preg_match('/^[a-z0-9_]+$/', $name));

        return array_values($valid);
    }
}
