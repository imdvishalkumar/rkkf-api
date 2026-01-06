<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variation;
use App\Services\ProductService;
use App\Services\BeltService;
use App\Helpers\ApiResponseHelper;
use App\Helpers\PaginationHelper;
use App\Http\Resources\ProductListResource;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

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
                'per_page' => 'nullable|integer|min:1|max:' . PaginationHelper::getMaxPerPage(),
                'page' => 'nullable|integer|min:1',
            ]);

            $beltId = $request->input('belt_id');
            // Pagination parameters
            $perPage = $request->input('per_page', PaginationHelper::getDefaultPerPage());
            $page = $request->input('page', 1);

            // Get paginated products (join with variations, qty > 0)
            $paginator = $this->productService->getProductList($beltId, $perPage, $page);

            // Use resource to sanitize each item and avoid exposing model internals
            $items = array_map(function ($row) {
                return (new ProductListResource($row))->resolve();
            }, $paginator->items());

            $paginationData = PaginationHelper::formatWithData($paginator, $items);

            $response = [
                'status' => true,
                'message' => 'Products retrieved successfully',
                'data' => $paginationData,
            ];

            // If no belt_id filter, also include belts list (preserve legacy field)
            if (!$beltId) {
                $response['belt'] = $this->beltService->getAllBelts();
            }

            return response()->json($response, 200);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get all products (CRUD - READ)
     * GET /api/products?per_page=10&page=1
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'is_active' => 'nullable|boolean',
                'per_page' => 'nullable|integer|min:1|max:' . PaginationHelper::getMaxPerPage(),
                'page' => 'nullable|integer|min:1',
            ]);

            $query = Product::query();

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Get pagination parameters
            $perPage = $request->input('per_page', PaginationHelper::getDefaultPerPage());
            $page = $request->input('page', 1);
            
            // Get with pagination
            $products = $query->paginate($perPage, ['*'], 'page', $page);

            // Format products with image URLs
            $products->getCollection()->transform(function ($product) {
                return $this->formatProductWithVariations($product);
            });

            // Custom pagination format
            $paginationData = PaginationHelper::formatWithData($products, $products->items());

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully',
                'data' => $paginationData,
            ], 200);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get single product by ID (CRUD - READ)
     * GET /api/products/{product_id}
     */
    public function show($productId)
    {
        try {
            $product = Product::with('variations')->findOrFail($productId);
            
            $product = $this->formatProductWithVariations($product);

            return response()->json([
                'status' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product,
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Product not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Create new product (CRUD - CREATE)
     * POST /api/products
     */
    public function store(Request $request)
    {
        try {
                $validated = $request->validate([
                    'name' => 'required|string|max:50',
                    'details' => 'required|string|max:300',
                    'image1' => 'required|file|image|mimes:jpeg,jpg,png|max:5120',
                    'image2' => 'nullable|file|image|mimes:jpeg,jpg,png|max:5120',
                    'image3' => 'nullable|file|image|mimes:jpeg,jpg,png|max:5120',
                    'belt_ids' => 'required|string|max:256',
                    'is_active' => 'required',
                    'variations' => 'required',
                ]);

            // Normalize belt_ids (trim extra quotes if present)
            $beltIdsRaw = $request->input('belt_ids', '');
            $beltIds = trim($beltIdsRaw, "\"' ");

            // Parse is_active (allow "true"/"false" strings)
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $isActive = $isActive === null ? 0 : (int) $isActive;

            // Parse variations: support JSON string or array
            $variationsInput = $request->input('variations');
            if (is_string($variationsInput)) {
                $variations = json_decode($variationsInput, true);
                if (!is_array($variations)) {
                    return ApiResponseHelper::error('Invalid variations format', 422);
                }
            } elseif (is_array($variationsInput)) {
                $variations = $variationsInput;
            } else {
                return ApiResponseHelper::error('Invalid variations format', 422);
            }

            // Validate each variation entry
            foreach ($variations as $v) {
                if (!isset($v['variation']) || !isset($v['price']) || !isset($v['qty'])) {
                    return ApiResponseHelper::error('Each variation must include variation, price and qty', 422);
                }
            }

            // Prepare image directory
            $imageDir = public_path('images/products');
            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            // Helper to store uploaded file and return safe filename
            $storeFile = function ($file, $baseName) use ($imageDir) {
                $ext = $file->getClientOriginalExtension() ?: $file->extension();
                $safeBase = Str::slug($baseName ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $filename = $safeBase . '-' . time() . '-' . substr(md5(uniqid((string) rand(), true)), 0, 6) . '.' . $ext;
                $file->move($imageDir, $filename);
                return $filename;
            };

            // Store images
            $image1Name = $request->file('image1') ? $storeFile($request->file('image1'), $request->input('name')) : null;
            $image2Name = $request->file('image2') ? $storeFile($request->file('image2'), $request->input('name') . '-2') : null;
            $image3Name = $request->file('image3') ? $storeFile($request->file('image3'), $request->input('name') . '-3') : null;

            // Create product
            $product = Product::create([
                'name' => $request->input('name'),
                'details' => $request->input('details'),
                'image1' => $image1Name,
                'image2' => $image2Name,
                'image3' => $image3Name,
                'belt_ids' => $beltIds,
                'is_active' => $isActive,
            ]);

            // Create variations
            foreach ($variations as $variationData) {
                Variation::create([
                    'product_id' => $product->product_id,
                    'variation' => $variationData['variation'],
                    'price' => $variationData['price'],
                    'qty' => $variationData['qty'],
                ]);
            }

            // Reload with relations
            $product = Product::with('variations')->find($product->product_id);
            $product = $this->formatProductWithVariations($product);

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 400));
        }
    }

    /**
     * Update product (CRUD - UPDATE)
     * PUT /api/products/{product_id}
     */
    public function update(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $validated = $request->validate([
                'name' => 'nullable|string|max:50',
                'details' => 'nullable|string|max:300',
                'image1' => 'nullable|string|max:500',
                'image2' => 'nullable|string|max:500',
                'image3' => 'nullable|string|max:500',
                'belt_ids' => 'nullable|string|max:256',
                'is_active' => 'nullable|boolean',
                'variations' => 'nullable|array',
                'variations.*.id' => 'nullable|integer',
                'variations.*.variation' => 'required_with:variations|string|max:50',
                'variations.*.price' => 'required_with:variations|numeric|min:0',
                'variations.*.qty' => 'required_with:variations|integer|min:0',
            ]);

            // Update product fields
            if (isset($validated['name'])) {
                $product->name = $validated['name'];
            }
            if (isset($validated['details'])) {
                $product->details = $validated['details'];
            }
            if (isset($validated['image1'])) {
                $product->image1 = $validated['image1'];
            }
            if (isset($validated['image2'])) {
                $product->image2 = $validated['image2'];
            }
            if (isset($validated['image3'])) {
                $product->image3 = $validated['image3'];
            }
            if (isset($validated['belt_ids'])) {
                $product->belt_ids = $validated['belt_ids'];
            }
            if (isset($validated['is_active'])) {
                $product->is_active = $validated['is_active'];
            }

            $product->save();

            // Update variations if provided
            if (isset($validated['variations'])) {
                // Delete existing variations
                $product->variations()->delete();

                // Create new variations
                foreach ($validated['variations'] as $variationData) {
                    Variation::create([
                        'product_id' => $product->product_id,
                        'variation' => $variationData['variation'],
                        'price' => $variationData['price'],
                        'qty' => $variationData['qty'],
                    ]);
                }
            }

            // Reload with relations
            $product = Product::with('variations')->find($product->product_id);
            $product = $this->formatProductWithVariations($product);

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Product not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 400));
        }
    }

    /**
     * Delete product (CRUD - DELETE)
     * DELETE /api/products/{product_id}
     */
    public function destroy($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Delete variations
            $product->variations()->delete();

            // Delete product
            $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Product not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Update product variation quantity (Helper method)
     * PUT /api/products/{product_id}/variations/{variation_id}
     */
    public function updateVariationQty(Request $request, $productId, $variationId)
    {
        try {
            $product = Product::findOrFail($productId);
            $variation = $product->variations()->findOrFail($variationId);

            $validated = $request->validate([
                'qty' => 'required|integer|min:0',
                'price' => 'nullable|numeric|min:0',
            ]);

            $variation->qty = $validated['qty'];
            if (isset($validated['price'])) {
                $variation->price = $validated['price'];
            }
            $variation->save();

            return response()->json([
                'status' => true,
                'message' => 'Variation updated successfully',
                'data' => $variation,
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Product or variation not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 400));
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

    /**
     * Format single product with variations and image URLs
     */
    protected function formatProductWithVariations($product)
    {
        $baseUrl = config('app.url') . '/images/products/';
        $placeholder = $baseUrl . 'placeholder.png';

        $product->image1 = $product->image1 ? $baseUrl . $product->image1 : $placeholder;
        $product->image2 = $product->image2 ? $baseUrl . $product->image2 : $placeholder;
        $product->image3 = $product->image3 ? $baseUrl . $product->image3 : $placeholder;

        return $product;
    }
}

