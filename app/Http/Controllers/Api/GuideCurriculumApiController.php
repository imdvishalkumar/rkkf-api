<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuideCurriculum;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class GuideCurriculumApiController extends Controller
{
    /**
     * Get Guide Curriculum List
     * GET /api/guide-curriculum?type=faq
     */
    public function index(Request $request)
    {
        try {
            $type = $request->input('type', 'faq'); // Default to FAQ

            $items = GuideCurriculum::where('active', 1)
                ->where('type', $type)
                ->orderBy('sort_order', 'asc')
                ->get();

            $formattedItems = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'subtitle' => $item->subtitle,
                    'language' => $item->language,
                    'icon' => $item->icon ? asset($item->icon) : null,
                    'file_url' => $item->file_url,
                    'download_url' => $item->file_url ? route('api.guide-curriculum.download', ['id' => $item->id]) : null,
                    'type' => $item->type,
                ];
            });

            // Calculate languages count
            $languagesCount = $items->pluck('language')->unique()->count();

            return ApiResponseHelper::success([
                'items' => $formattedItems,
                'languages_count' => $languagesCount
            ], 'Guide curriculum retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Download Guide Curriculum File
     * GET /api/guide-curriculum/{id}/download
     */
    public function download($id)
    {
        try {
            $item = GuideCurriculum::find($id);

            if (!$item) {
                return ApiResponseHelper::error('Guide curriculum not found', 404);
            }

            if (!$item->file_url) {
                return ApiResponseHelper::error('No file available for this item', 404);
            }

            // Extract filename from the complete URL
            $parsedUrl = parse_url($item->file_url);
            $urlPath = $parsedUrl['path'] ?? '';

            // Remove leading slash if present
            $relativePath = ltrim($urlPath, '/');
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                return ApiResponseHelper::error('File not found on server', 404);
            }

            // Generate a safe filename for download
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $item->title) . '.' . $extension;

            return response()->download($filePath, $safeFilename, [
                'Content-Type' => mime_content_type($filePath),
            ]);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
