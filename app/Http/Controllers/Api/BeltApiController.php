<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BeltService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;

class BeltApiController extends Controller
{
    protected $beltService;

    public function __construct(BeltService $beltService)
    {
        $this->beltService = $beltService;
    }

    /**
     * List all belts
     */
    public function index()
    {
        try {
            $belts = $this->beltService->getAllBelts();
            return ApiResponseHelper::success(
                $belts,
                'Belts retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
