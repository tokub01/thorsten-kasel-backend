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
            $sort = $request->query('sort', 'desc'); // ✅ 'desc' oder 'asc'

            $products = Product::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                          ->orWhere('description', 'like', '%' . $keyword . '%');
                })
                ->when(!is_null($category) && $category !== '', function ($query) use ($category) {
                    $query->where('category_id', $category);
                })
                ->when(in_array($sort, ['desc', 'asc']), function ($query) use ($sort) {
                    $query->orderBy('created_at', $sort);
                })
                ->with('category') // ✅ Category eager loading
                ->get();

            return (new ProductResourceCollection($products))->toResponse($request);

        } catch (Throwable $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve products.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
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
            // ✅ Category laden
            $product->load('category');

            return response()->json(new ProductResource($product), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the product.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
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

            // Bild nur speichern wenn vorhanden
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 's3');
                $data['image'] = $path;
            } else {
                $data['image'] = null;
            }

            $product = Product::create($data);

            // ✅ Category laden für Response
            $product->load('category');

            // ✅ Nur das neue Produkt zurückgeben, nicht alle
            return response()->json(new ProductResource($product), 201);

        } catch (Throwable $e) {
            Log::error('Failed to store product: ' . $e->getMessage(), [
                'request_data' => $request->except('image'),
                'has_file' => $request->hasFile('image'),
            ]);

            return response()->json([
                'message' => 'Unable to store product.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
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

            // Nur Bild aktualisieren wenn neues hochgeladen wurde
            if ($request->hasFile('image')) {
                // Altes Bild löschen, falls vorhanden
                if ($product->image &&
                    Storage::disk('s3')->exists($product->image) &&
                    !str_contains($product->image, 'placeholder')) {
                    Storage::disk('s3')->delete($product->image);
                }

                // Neues Bild hochladen
                $path = $request->file('image')->store('products', 's3');
                $data['image'] = $path;
            } else {
                // ✅ Bild-Feld aus Update entfernen wenn kein neues Bild
                unset($data['image']);
            }

            $product->update($data);

            // ✅ Category laden für Response
            $product->load('category');

            return response()->json(new ProductResource($product->fresh()), 200);

        } catch (Throwable $e) {
            Log::error('Failed to update product: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'request_data' => $request->except('image'),
            ]);

            return response()->json([
                'message' => 'Unable to update product.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
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
            // Bild löschen falls vorhanden und nicht Placeholder
            if ($product->image &&
                Storage::disk('s3')->exists($product->image) &&
                !str_contains($product->image, 'placeholder')) {
                Storage::disk('s3')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200);

        } catch (Throwable $e) {
            Log::error('Failed to delete product: ' . $e->getMessage(), [
                'product_id' => $product->id,
            ]);

            return response()->json([
                'message' => 'Unable to delete product.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}
