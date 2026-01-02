<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Belt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('variations')->get();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $belts = Belt::all();
        return view('products.create', compact('belts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'belt' => 'required|array',
            'variation' => 'required|array',
            'variation.*' => 'required|string',
            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',
            'qty' => 'required|array',
            'qty.*' => 'required|integer|min:0',
            'img1' => 'nullable|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
        ]);

        DB::beginTransaction();
        try {
            $beltStr = implode(', ', $validated['belt']);
            
            // Handle image uploads
            $image1 = $this->uploadImage($request->file('img1'), $validated['product_name'], 1);
            $image2 = $this->uploadImage($request->file('img2'), $validated['product_name'], 2);
            $image3 = $this->uploadImage($request->file('img3'), $validated['product_name'], 3);

            $product = Product::create([
                'name' => $validated['product_name'],
                'details' => $validated['description'] ?? null,
                'image1' => $image1,
                'image2' => $image2,
                'image3' => $image3,
                'belt_ids' => $beltStr,
                'active' => 1,
            ]);

            // Create variations
            foreach ($validated['variation'] as $index => $variation) {
                $product->variations()->create([
                    'variation' => $variation,
                    'price' => $validated['price'][$index],
                    'qty' => $validated['qty'][$index],
                ]);
            }

            DB::commit();
            return redirect()->route('products.index')
                ->with('success', 'Product added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error adding product: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('variations');
        $belts = Belt::all();
        return view('products.edit', compact('product', 'belts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'belt' => 'required|array',
        ]);

        $beltStr = implode(', ', $validated['belt']);
        
        $product->update([
            'name' => $validated['product_name'],
            'details' => $validated['description'] ?? null,
            'belt_ids' => $beltStr,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function uploadImage($file, $productName, $index)
    {
        if (!$file) {
            return null;
        }

        $extension = $file->getClientOriginalExtension();
        $filename = uniqid("{$index}_{$productName}_", true);
        $filename = str_replace('.', '', $filename) . '.' . $extension;
        
        $file->move(public_path('images/products'), $filename);
        
        return $filename;
    }
}
