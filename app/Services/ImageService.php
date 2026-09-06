<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;
/**
 * Class ImageService.
 */
class ImageService extends BaseService
{
    public function uploadProfilePicture(UploadedFile $file) : string
    {
        $filename = random_int(1000000000000000, 9999999999999999) . '.' . $file->extension();
        try {
            $path = $file->storeAs('images/users/profile_pictures', $filename, 'public');
            Log::info('Uploaded File Path: ' . $path);
            return $path;
        } catch (Exception $exception) {
            Log::error('Profile Picture Upload Failed: ' . $exception->getMessage());
            throw new Exception('Failed to Upload Profile Picture');
        }
    }

    public function uploadPicture(UploadedFile $file) : string
    {
        $filename = random_int(1000000000000000, 9999999999999999) . '.' . $file->extension();
        try {
            $path = $file->storeAs('images/teams', $filename, 'public');
            Log::info('Uploaded File Path: ' . $path);
            return $path;
        } catch (Exception $exception) {
            Log::error('Picture Upload Failed: ' . $exception->getMessage());
            throw new Exception('Failed to Upload Picture');
        }
    }

    public function uploadPhoto(UploadedFile $file ) : string
    {
        $filename = random_int(1000000000000000, 9999999999999999) . '.' . $file->extension();
        try {
            $path = $file->storeAs('images/employees/photo', $filename, 'public');
            Log::info('Uploaded File Path: ' . $path);
            return $path;
        } catch (Exception $exception) {
            Log::error('Picture Upload Failed: ' . $exception->getMessage());
            throw new Exception('Failed to Upload Photo');
        }
    }

    public function uploadSignature(UploadedFile $file ) : string
    {
        $filename = random_int(1000000000000000, 9999999999999999) . '.' . $file->extension();
        try {
            $path = $file->storeAs('images/employees/signature', $filename, 'public');
            Log::info('Uploaded File Path: ' . $path);
            return $path;
        } catch (Exception $exception) {
            Log::error('Picture Upload Failed: ' . $exception->getMessage());
            throw new Exception('Failed to Upload Signature');
        }
    }

    public function uploadImage(UploadedFile $file, string $type): string
    {
        $paths = config('upload.paths');

        if (!isset($paths[$type])) {
            throw new Exception("Your File Type is Invalid. Type: {$type}");
        }

        $filename = random_int(1000000000000000, 9999999999999999) . '.' . $file->extension();

        try {
            $path = $file->storeAs($paths[$type], $filename, 'public');
            Log::info('Uploaded Image Path: ' . $path);
            return $path;
        } catch (Exception $exception) {
            Log::error('Image Upload Failed: ' . $exception->getMessage());
            throw new Exception('Failed to Upload Image. File Type: ' . $type . 'Exception' . $exception->getMessage());
        }
    }
}