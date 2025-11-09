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
    public function index(Request $request): JsonResponse
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
    public function show(Request $request, $category): JsonResponse
    {
        try {
            // Laravel Route Model Binding macht aus {category} automatisch ein Model
            // Oder es ist eine ID als String
            if (is_object($category)) {
                $categoryModel = $category;
            } else {
                $categoryModel = Category::with('product')->find($category);
            }

            if (!$categoryModel) {
                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            return (new CategoryResource($categoryModel))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to fetch category', [
                'category' => $category,
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
    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            Log::info('Creating category', [
                'data' => $data,
            ]);

            $category = Category::create($data);
            $category->load('product');

            return (new CategoryResource($category))
                ->toResponse($request)
                ->setStatusCode(201);
        } catch (Throwable $e) {
            Log::error('Failed to create category', [
                'data' => $request->all(),
                'validated' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
    public function update(CategoryRequest $request, $category): JsonResponse
    {
        try {
            // Laravel Route Model Binding macht aus {category} automatisch ein Model
            // Oder es ist eine ID als String
            if (is_object($category)) {
                $categoryModel = $category;
                $id = $category->id;
            } else {
                $id = $category;
                $categoryModel = Category::find($id);
            }

            // Logge alle eingehenden Daten für Debugging
            Log::info('Update request received', [
                'category_param' => $category,
                'id' => $id,
                'all_input' => $request->all(),
                'method' => $request->method(),
                '_method' => $request->input('_method'),
                'has_name' => $request->has('name'),
                'name_value' => $request->input('name'),
                'has_product_id' => $request->has('product_id'),
                'product_id_value' => $request->input('product_id'),
            ]);

            if (!$categoryModel) {
                Log::warning('Category not found for update', ['id' => $id]);

                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            // Hole validierte Daten
            $data = $request->validated();

            Log::info('Updating category with validated data', [
                'id' => $id,
                'current_name' => $categoryModel->name,
                'new_data' => $data,
            ]);

            // Update durchführen
            $categoryModel->update($data);

            // Lade Beziehungen neu
            $categoryModel->load('product');

            // Hole frische Instanz aus DB
            $categoryModel = $categoryModel->fresh(['product']);

            Log::info('Category updated successfully', [
                'id' => $id,
                'updated_category' => $categoryModel->toArray(),
            ]);

            return (new CategoryResource($categoryModel))->toResponse($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation Fehler explizit loggen
            Log::error('Validation failed for category update', [
                'category' => $category,
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Validierungsfehler.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Failed to update category', [
                'category' => $category,
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
    public function destroy(Request $request, $category): JsonResponse
    {
        try {
            // Laravel Route Model Binding macht aus {category} automatisch ein Model
            // Oder es ist eine ID als String
            if (is_object($category)) {
                $categoryModel = $category;
            } else {
                $categoryModel = Category::find($category);
            }

            if (!$categoryModel) {
                return response()->json([
                    'message' => 'Kategorie nicht gefunden.',
                ], 404);
            }

            $categoryModel->delete();

            return response()->json([
                'message' => 'Kategorie erfolgreich gelöscht.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete category', [
                'category' => $category,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Fehler beim Löschen der Kategorie.',
                'error' => config('app.debug') ? $e->getMessage() : 'Ein Fehler ist aufgetreten',
            ], 500);
        }
    }
}
