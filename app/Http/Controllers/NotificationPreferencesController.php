<?php

namespace App\Http\Controllers;

use App\Exceptions\GeneralException;
use App\Http\Requests\NotificationPreference\UpdateNotificationPreferenceRequest;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lets the logged-in user switch a notification channel on or off for themselves.
 */
class NotificationPreferencesController extends Controller
{
    /**
     * Keeps the service that saves the user's choices.
     */
    public function __construct(protected UserNotificationPreferenceService $preferenceService) {}

    /**
     * Saves the user's on/off choice for one channel.
     */
    public function update(UpdateNotificationPreferenceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $this->preferenceService->setPreference(
                $request->user(),
                (int) $validated['channel_id'],
                (bool) $validated['is_enabled'],
            );

            return response()->json(['success' => true, 'message' => 'Notification Preference Saved.']);
        } catch (GeneralException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Notification Preference Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Saving the Preference.'], 500);
        }
    }
}
