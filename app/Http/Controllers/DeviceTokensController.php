<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceToken\DeviceTokenRequest;
use App\Services\DeviceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lets the logged-in user register or remove the push token of their own browser/phone.
 */
class DeviceTokensController extends Controller
{
    /**
     * Keeps the service that saves device tokens.
     */
    public function __construct(protected DeviceTokenService $deviceTokenService) {}

    /**
     * Saves (or refreshes) the token of the current device.
     */
    public function store(DeviceTokenRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $this->deviceTokenService->register(
                $request->user(),
                $validated['token'],
                $validated['platform'] ?? 'web',
                $validated['device_name'] ?? null,
            );

            return response()->json(['success' => true, 'message' => 'Device Registered Successfully.']);
        } catch (Throwable $exception) {
            Log::error('Device Token Registration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Registering the Device.'], 500);
        }
    }

    /**
     * Removes the token of the current device (for example when the user turns push off).
     */
    public function destroy(DeviceTokenRequest $request): JsonResponse
    {
        try {
            $this->deviceTokenService->unregister($request->user(), $request->validated()['token']);

            return response()->json(['success' => true, 'message' => 'Device Removed Successfully.']);
        } catch (Throwable $exception) {
            Log::error('Device Token Removal Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Removing the Device.'], 500);
        }
    }
}
