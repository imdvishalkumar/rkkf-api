<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactInfo;
use App\Helpers\ApiResponseHelper;
use Exception;

class ContactInfoController extends Controller
{
    /**

    /**
     * Get Contact Info
     * GET /api/contact-info
     */
    public function index()
    {
        try {
            $contactInfo = ContactInfo::find(1);
            if (!$contactInfo) {
                // Return empty structure or default
                return ApiResponseHelper::success(null, 'No contact info found');
            }
            return ApiResponseHelper::success($contactInfo, 'Contact info retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update/Insert Contact Info
     * POST /api/contact-info
     */
    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'mobile_number' => 'nullable|string',
                'email' => 'nullable|email',
                'whatsapp_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ApiResponseHelper::error($validator->errors()->first(), 200);
            }

            $contactInfo = ContactInfo::updateOrCreate(
                ['id' => 1], // Always update the first record
                [
                    'mobile_number' => $request->mobile_number,
                    'email' => $request->email,
                    'whatsapp_number' => $request->whatsapp_number,
                ]
            );

            return ApiResponseHelper::success($contactInfo, 'Contact info updated successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
