<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderApiController extends Controller
{
    protected $orderService;
    protected $productService;

    public function __construct(
        OrderService $orderService,
        ProductService $productService
    ) {
        $this->orderService = $orderService;
        $this->productService = $productService;
    }

    /**
     * Get orders list
     * GET /api/orders/get-orders?param=true
     */
    public function getOrders(Request $request)
    {
        try {
            $request->validate([
                'param' => 'nullable|string',
            ]);

            $param = $request->input('param');

            $query = DB::table('orders as o')
                ->join('students as s', 'o.student_id', '=', 's.student_id')
                ->select(
                    'o.*',
                    DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                    's.student_id as grno'
                );

            if ($param === 'true') {
                // Additional filtering if needed
                $query->where('o.viewed', 0); // Unviewed orders
            }

            $orders = $query->orderBy('o.created_at', 'desc')->get();

            return ApiResponseHelper::success($orders, 'Orders retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Mark order as viewed (POST)
     */
    public function markViewed(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,order_id',
            ]);

            $orderId = $request->input('order_id');

            DB::table('orders')
                ->where('order_id', $orderId)
                ->update(['viewed' => 1]);

            return ApiResponseHelper::success(null, 'Order marked as viewed');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Mark order as delivered (POST)
     */
    public function markDelivered(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,order_id',
            ]);

            $orderId = $request->input('order_id');

            DB::table('orders')
                ->where('order_id', $orderId)
                ->update(['delivered' => 1]);

            return ApiResponseHelper::success(null, 'Order marked as delivered');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Delete product (POST)
     */
    public function deleteProduct(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,product_id',
            ]);

            $productId = $request->input('product_id');
            $deleted = $this->productService->deleteProduct($productId);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'Product deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete product', 500);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
