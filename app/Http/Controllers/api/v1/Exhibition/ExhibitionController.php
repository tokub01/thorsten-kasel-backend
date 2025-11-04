<?php

namespace App\Http\Controllers\api\v1\Exhibition;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Exhibition\StoreExhibitionRequest;
use App\Http\Requests\api\v1\Exhibition\UpdateExhibitionRequest;
use App\Http\Resources\api\v1\Exhibition\ExhibitionResource;
use App\Http\Resources\api\v1\Exhibition\ExhibitionResourceCollection;
use App\Models\Exhibition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

/**
 * @group Exhibition Management
 *
 * Controller for handling exhibition-related operations (CRUD) in API v1.
 */
class ExhibitionController extends Controller
{
    /**
     * Display a listing of all exhibitions.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $keyword = trim($request->query('keyword', ''));
            $sort = $request->query('sort', 'desc'); // ✅ 'desc' oder 'asc' statt 'newest'/'oldest'

            $exhibitions = Exhibition::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                          ->orWhere('description', 'like', '%' . $keyword . '%'); // ✅ Auch Beschreibung durchsuchen
                })
                ->when(in_array($sort, ['desc', 'asc']), function ($query) use ($sort) {
                    $query->orderBy('created_at', $sort);
                })
                ->get();

            return (new ExhibitionResourceCollection($exhibitions))->toResponse($request);

        } catch (Throwable $e) {
            Log::error('Failed to fetch exhibitions: ' . $e->getMessage()); // ✅ Korrekter Text

            return response()->json([
                'message' => 'Unable to retrieve exhibitions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified exhibition article.
     *
     * @param Exhibition $exhibition
     * @return JsonResponse
     */
    public function show(Exhibition $exhibition): JsonResponse
    {
        try {
            return response()->json(new ExhibitionResource($exhibition), 200); // ✅ $exhibition statt $news
        } catch (Throwable $e) {
            Log::error('Failed to fetch exhibition: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the exhibition article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created exhibition article in storage.
     *
     * @param StoreExhibitionRequest $request
     * @return JsonResponse
     */
    public function store(StoreExhibitionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('exhibitions', 's3');
                $data['image'] = $path;
            }

            $exhibition = Exhibition::create($data); // ✅ $exhibition statt $product

            // ✅ Gib die neu erstellte Exhibition zurück, nicht alle
            return response()->json(new ExhibitionResource($exhibition), 201);

        } catch (Throwable $e) {
            Log::error('Failed to store exhibition: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to store exhibition article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified exhibition article in storage.
     *
     * @param UpdateExhibitionRequest $request
     * @param Exhibition $exhibition
     * @return JsonResponse
     */
    public function update(UpdateExhibitionRequest $request, Exhibition $exhibition): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                // Altes Bild löschen, falls vorhanden
                if ($exhibition->image && Storage::disk('s3')->exists($exhibition->image)) {
                    Storage::disk('s3')->delete($exhibition->image);
                }

                // Neues Bild hochladen
                $path = $request->file('image')->store('exhibitions', 's3');
                $data['image'] = $path;
            }

            $exhibition->update($data);
            
            return response()->json(new ExhibitionResource($exhibition->fresh()), 200);

        } catch (Throwable $e) {
            Log::error('Failed to update exhibition article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update exhibition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified exhibition article from storage.
     *
     * @param Exhibition $exhibition
     * @return JsonResponse
     */
    public function destroy(Exhibition $exhibition): JsonResponse
    {
        try {
            if ($exhibition->image && Storage::disk('s3')->exists($exhibition->image) && !str_contains($exhibition->image, 'placeholder')) {
                Storage::disk('s3')->delete($exhibition->image);
            }

            $exhibition->delete();

            return response()->json([
                'message' => 'Exhibition deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete exhibition article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete specified exhibition article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
