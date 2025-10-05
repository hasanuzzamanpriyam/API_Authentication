<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ProductController extends Controller
{
public function index(Request $request)
{
    try {
        // Base query
        $qry = Product::query();

        // ✅ Search by name, brand, or category
        if ($request->filled('search')) {
            $search = $request->search;
            $qry->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Order by created_at (default desc)
        if ($request->filled('order') && strtolower($request->order) === 'asc') {
            $qry->orderBy('created_at', 'asc');
        } else {
            $qry->orderBy('created_at', 'desc');
        }

        // ✅ Determine per page
        $perPage = $request->page === '0' ? Product::count() : $request->input('per_page', 10);

        // ✅ Paginate results
        $data = $qry->paginate($perPage);

        // ✅ Map products with full image URL
        $products = $data->getCollection()->map(function ($product) {
            return [
                'id'             => $product->id,
                'name'           => $product->name,
                'price'          => $product->price,
                'image'          => $product->image ? asset('storage/' . $product->image) : null,
                'category'       => $product->category,
                'brand'          => $product->brand,
                'rating'         => $product->rating,
                'count_in_stock' => $product->count_in_stock,
                'num_reviews'    => $product->num_reviews,
                'status'         => $product->status,
                'created_at'     => $product->created_at,
                'updated_at'     => $product->updated_at,
            ];
        });

        // ✅ Return paginated response
        return response()->json([
            'pagination' => [
                'limit_page'         => $perPage,
                'total_count'        => $data->total(),
                'total_page'         => $data->lastPage(),
                'current_page'       => $data->currentPage(),
                'current_page_count' => $data->count(),
                'next_page'          => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                'previous_page'      => $data->onFirstPage() ? null : $data->currentPage() - 1,
            ],
            'message' => 'Products retrieved successfully',
            'data'    => $products,
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}



    public function store(Request $request)
    {
        try {
            // ✅ Validate input
            $validated = $request->validate([
                'name'           => 'required|string|max:255',
                'price'          => 'required|numeric',
                'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'category'       => 'nullable|string|max:255',
                'brand'          => 'nullable|string|max:255',
                'rating'         => 'nullable|numeric|min:0|max:5',
                'count_in_stock' => 'nullable|integer|min:0',
                'num_reviews'    => 'nullable|integer|min:0',
                'status'         => 'nullable|in:active,inactive,pending',
            ]);

            // ✅ Handle image upload (if provided)
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // ✅ Create new product
            $product = Product::create([
                'name'           => $validated['name'],
                'price'          => $validated['price'],
                'image'          => $imagePath ?? null,
                'category'       => $validated['category'] ?? null,
                'brand'          => $validated['brand'] ?? null,
                'rating'         => $validated['rating'] ?? null,
                'count_in_stock' => $validated['count_in_stock'] ?? null,
                'num_reviews'    => $validated['num_reviews'] ?? null,
                'status'         => $validated['status'] ?? 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data'    => [
                    'product' => $product->name,
                    'price'   => $product->price,
                    'image'   => $product->image ? asset('storage/' . $product->image) : null,
                    'category' => $product->category,
                    'brand'   => $product->brand,
                    'rating'  => $product->rating,
                    'count_in_stock' => $product->count_in_stock,
                    'num_reviews' => $product->num_reviews,
                    'status'  => $product->status,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // ✅ Find the product
            $product = Product::findOrFail($id);

            // ✅ Validate input
            $validated = $request->validate([
                'name'           => 'sometimes|string|max:255',
                'price'          => 'sometimes|numeric',
                'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'category'       => 'sometimes|string|max:255',
                'brand'          => 'sometimes|string|max:255',
                'rating'         => 'sometimes|numeric|min:0|max:5',
                'count_in_stock' => 'sometimes|integer|min:0',
                'num_reviews'    => 'sometimes|integer|min:0',
                'status'         => 'sometimes|in:active,inactive,pending',
            ]);

            // ✅ Handle image upload (replace old if provided)
            if ($request->hasFile('image')) {
                // Optional: delete old image from storage
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // ✅ Update product
            $product->update($validated);

            // ✅ Full image URL
            $fullImageUrl = $product->image ? asset('storage/' . $product->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data'    => [
                    'name'           => $product->name,
                    'price'          => $product->price,
                    'image'          => $fullImageUrl,
                    'category'       => $product->category,
                    'brand'          => $product->brand,
                    'rating'         => $product->rating,
                    'count_in_stock' => $product->count_in_stock,
                    'num_reviews'    => $product->num_reviews,
                    'status'         => $product->status,
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            // ✅ Find product by ID
            $product = Product::findOrFail($id);

            // ✅ Full image URL
            $fullImageUrl = $product->image ? asset('storage/' . $product->image) : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'price'          => $product->price,
                    'image'          => $fullImageUrl,
                    'category'       => $product->category,
                    'brand'          => $product->brand,
                    'rating'         => $product->rating,
                    'count_in_stock' => $product->count_in_stock,
                    'num_reviews'    => $product->num_reviews,
                    'status'         => $product->status,
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // ✅ Find the product
            $product = Product::findOrFail($id);

            // ✅ Delete image from storage if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // ✅ Delete the product record
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
