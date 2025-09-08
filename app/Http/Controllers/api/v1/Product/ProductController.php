<?php

namespace App\Http\Controllers\api\v1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Product\ProductRequest;
use App\Http\Responses\api\v1\Product\ProductResource;
use App\Http\Responses\api\v1\Product\ProductResourceCollection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

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
    public function index(): JsonResponse
    {
        try {
            $products = Product::all();
            return response()->json(new ProductResourceCollection($products), 200);
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
            $product = Product::create($data);

            return response()->json(new ProductResource($product), 201);
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
            $product->update($data);

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
