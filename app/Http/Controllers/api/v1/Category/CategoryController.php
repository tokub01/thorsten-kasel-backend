<?php

namespace App\Http\Controllers\api\v1\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Category\CategoryRequest;
use App\Http\Responses\api\v1\Category\CategoryResource;
use App\Http\Responses\api\v1\Category\CategoryResourceCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @group Category Management
 *
 * Handles all category-related API operations.
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function index(CategoryRequest $request): JsonResponse
    {
        try {
            $categories = Category::all();
            return response()->json(new CategoryResourceCollection($categories), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch categories: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category.
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return JsonResponse
     */
    public function show(CategoryRequest $request, Category $category): JsonResponse
    {
        try {
            return response()->json(new CategoryResource($category), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     *
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $category = Category::create($data);

            return response()->json(new CategoryResource($category), 201);
        } catch (Throwable $e) {
            Log::error('Failed to create category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to create category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified category.
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return JsonResponse
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $data = $request->validated();
            $category->update($data);

            return response()->json(new CategoryResource($category), 200);
        } catch (Throwable $e) {
            Log::error('Failed to update category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     *
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function destroy(CategoryRequest $request): JsonResponse
    {
        try {
            $categoryId = $request->route('category');
            $category = Category::findOrFail($categoryId);
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete category: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
