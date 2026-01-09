<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Get all product categories
     * GET /api/product-categories
     */
    public function index(Request $request)
    {
        try {
            $query = ProductCategory::query();

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            $categories = $query->get();

            // Format image URL
            $categories->transform(function ($cat) {
                $cat->image = $cat->image ? url('images/categories/' . $cat->image) : null;
                return $cat;
            });

            return ApiResponseHelper::success($categories, 'Product categories retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Create product category (Admin only)
     * POST /api/product-categories
     */
    public function store(Request $request)
    {
        try {
            // Admin Check
            if (!$request->user() || !$request->user()->isAdmin()) {
                return ApiResponseHelper::forbidden('Only admins can create categories.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|file|image|max:5120',
                'active' => 'boolean',
            ]);

            $imageName = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destinationPath = public_path('images/categories');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $ext = $file->getClientOriginalExtension() ?: $file->extension();
                $imageName = Str::slug($request->input('name')) . '-' . time() . '.' . $ext;
                $file->move($destinationPath, $imageName);
            }

            $category = ProductCategory::create([
                'name' => $request->input('name'),
                'image' => $imageName,
                'active' => $request->boolean('active', true),
            ]);

            $category->image = $category->image ? url('images/categories/' . $category->image) : null;

            return ApiResponseHelper::success($category, 'Product category created successfully', 201);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update product category (Admin only)
     * PUT /api/product-categories/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || !$request->user()->isAdmin()) {
                return ApiResponseHelper::forbidden('Only admins can update categories.');
            }

            $category = ProductCategory::findOrFail($id);

            $request->validate([
                'name' => 'nullable|string|max:255',
                'image' => 'nullable|file|image|max:5120',
                'active' => 'nullable|boolean',
            ]);

            if ($request->has('name')) {
                $category->name = $request->input('name');
            }
            if ($request->has('active')) {
                $category->active = $request->boolean('active');
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image && file_exists(public_path('images/categories/' . $category->image))) {
                    unlink(public_path('images/categories/' . $category->image));
                }

                $file = $request->file('image');
                $destinationPath = public_path('images/categories');
                $ext = $file->getClientOriginalExtension() ?: $file->extension();
                $imageName = Str::slug($category->name) . '-' . time() . '.' . $ext;
                $file->move($destinationPath, $imageName);
                $category->image = $imageName;
            }

            $category->save();
            $category->image = $category->image ? url('images/categories/' . $category->image) : null;

            return ApiResponseHelper::success($category, 'Product category updated successfully');

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Category not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete product category (Admin only)
     * DELETE /api/product-categories/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || !$request->user()->isAdmin()) {
                return ApiResponseHelper::forbidden('Only admins can delete categories.');
            }

            $category = ProductCategory::findOrFail($id);

            // Check if products exist (prevent delete if used?)
            if ($category->products()->count() > 0) {
                return ApiResponseHelper::error('Cannot delete category with associated products.', 409);
            }

            if ($category->image && file_exists(public_path('images/categories/' . $category->image))) {
                unlink(public_path('images/categories/' . $category->image));
            }

            $category->delete();

            return ApiResponseHelper::success(null, 'Product category deleted successfully');

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponseHelper::error('Category not found', 404);
            }
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
