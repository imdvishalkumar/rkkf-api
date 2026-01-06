<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\BeltService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;

class ProductApiController extends Controller
{
    protected $productService;
    protected $beltService;

    public function __construct(
        ProductService $productService,
        BeltService $beltService
    ) {
        $this->productService = $productService;
        $this->beltService = $beltService;
    }

    /**
     * Get Product List with filters
     * GET /api/products/list?belt_id=1
     */
    public function getProductList(Request $request)
    {
        try {
            $request->validate([
                'belt_id' => 'nullable|integer|exists:belt,belt_id',
            ]);

            $beltId = $request->input('belt_id');
            
            // Get products with variations where qty > 0
            $products = $this->productService->getProductList($beltId);

            // Format products with image URLs
            $formattedProducts = $this->formatProducts($products);

            // Format response to match core PHP API format
            $response = [
                'success' => 1,
                'data' => $formattedProducts
            ];

            // If no belt_id filter, also include belts list
            if (!$beltId) {
                $belts = $this->beltService->getAllBelts();
                $response['belt'] = $belts;
            }

            // Return response matching core PHP API format exactly
            return response()->json($response, 200);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Format products with image URLs
     */
    protected function formatProducts($products)
    {
        $baseUrl = config('app.url') . '/images/products/';
        $placeholder = $baseUrl . 'placeholder.png';

        return $products->map(function ($product) use ($baseUrl, $placeholder) {
            // Format images
            $product->image1 = $product->image1 ? $baseUrl . $product->image1 : $placeholder;
            $product->image2 = $product->image2 ? $baseUrl . $product->image2 : $placeholder;
            $product->image3 = $product->image3 ? $baseUrl . $product->image3 : $placeholder;

            return $product;
        })->values();
    }
}

