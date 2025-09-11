<?php

namespace App\Http\Controllers\api\v1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Product\ProductRequest;
use App\Http\Resources\api\v1\Product\ProductResource;
use App\Http\Resources\api\v1\Product\ProductResourceCollection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
/**
 * @group Product Management
 *
 * Controller for handling product-related operations (CRUD) in API v1.
 */
class ProductController extends Controller
{
    /**
     * Display a listing of all products.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $keyword = trim($request->query('keyword', ''));
            $category = $request->query('category');
            $sort = $request->query('sort', 'newest'); // Default: neueste zuerst

            $products = Product::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%');
                })
                ->when(!is_null($category) && $category !== '', function ($query) use ($category) {
                    $query->where('category_id', $category);
                })
                ->when(in_array($sort, ['newest', 'oldest']), function ($query) use ($sort) {
                    if ($sort === 'newest') {
                        $query->orderBy('created_at', 'desc');
                    } else {
                        $query->orderBy('created_at', 'asc');
                    }
                })
                ->get();

            return (new ProductResourceCollection($products))->toResponse($request);

        } catch (Throwable $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        try {
            return response()->json(new ProductResource($product), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 's3'); // Ordner: products/
                $url = Storage::disk('s3')->url($path);

                $data['image'] = $path;
            }

            $product = Product::create($data);

            return (new ProductResourceCollection(Product::all()))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to store product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to store product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                // Altes Bild löschen, falls vorhanden
                if ($product->image && Storage::disk('s3')->exists($product->image)) {
                    Storage::disk('s3')->delete($product->image);
                }

                // Neues Bild hochladen
                $path = $request->file('image')->store('products', 's3');
                $data['image'] = $path;
            }

            $product->update($data);

            $product->save();

            return response()->json(new ProductResource($product), 200);
        } catch (Throwable $e) {
            Log::error('Failed to update product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            if ($product->image && Storage::disk('s3')->exists($product->image)) {
                Storage::disk('s3')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
