<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Upload image file
     * POST /api/upload
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            // Validate request has image file
            if (!$request->hasFile('image')) {
                return ApiResponseHelper::error('No image file provided', 422);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            ]);

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store in storage/app/public/images
            $path = $file->storeAs('images', $fileName, 'public');

            if ($path) {
                $url = Storage::disk('public')->url($path);

                return ApiResponseHelper::success([
                    'url' => $url,
                    'path' => $path,
                    'filename' => $fileName,
                ], 'Image uploaded successfully');
            } else {
                return ApiResponseHelper::error('Failed to upload image', 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to upload image',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
