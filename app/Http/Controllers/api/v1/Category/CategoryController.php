<?php

namespace App\Http\Controllers\api\v1\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Category\CategoryRequest;
use App\Http\Resources\api\v1\Category\CategoryResource;
use App\Http\Resources\api\v1\Category\CategoryResourceCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse  // ✅ Request statt CategoryRequest
    {
        try {
            $categories = Category::with('product')->get();

            return (new CategoryResourceCollection($categories))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to fetch categories', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Laden der Kategorien.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, $id): JsonResponse  // ✅ Request statt CategoryRequest
    {
        try {
            $category = Category::with('product')->find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            return (new CategoryResource($category))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to fetch category', [
                'category_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Laden der Kategorie.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): JsonResponse  // ✅ CategoryRequest
    {
        try {
            $data = $request->validated();

            $category = Category::create($data);
            $category->load('product');

            return (new CategoryResource($category))
                ->toResponse($request)
                ->setStatusCode(201);
        } catch (Throwable $e) {
            Log::error('Failed to create category', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Erstellen der Kategorie.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, $id): JsonResponse  // ✅ CategoryRequest
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            $data = $request->validated();

            Log::info('Updating category', [
                'id' => $id,
                'data' => $data,
            ]);

            $category->update($data);
            $category->load('product');

            return (new CategoryResource($category->fresh()))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to update category', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Aktualisieren der Kategorie.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, $id): JsonResponse  // ✅ Request statt CategoryRequest
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            $category->delete();

            return response()->json([
                'message' => 'Kategorie erfolgreich gelöscht.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete category', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Löschen der Kategorie.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }
}
