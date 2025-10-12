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
            $sort = $request->query('sort', 'newest'); // Default: neueste zuerst

            $exhibition = Exhibition::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%');
                })
                ->when(in_array($sort, ['newest', 'oldest']), function ($query) use ($sort) {
                    if ($sort === 'newest') {
                        $query->orderBy('created_at', 'desc');
                    } else {
                        $query->orderBy('created_at', 'asc');
                    }
                })
                ->get();

            return (new ExhibitionResourceCollection($exhibition))->toResponse($request);

        } catch (Throwable $e) {
            Log::error('Failed to fetch news: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve news.',
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
            return response()->json(new ExhibitionResource($news), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch exhibition: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the exhibitions article.',
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
                $path = $request->file('image')->store('exhibitions', 's3'); // Ordner: exhibitions/
                $url = Storage::disk('s3')->url($path);

                $data['image'] = $path;
            }

            $product = Exhibition::create($data);

            return (new ExhibitionResourceCollection(Exhibition::all()))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to store exhibition: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to store exhibitions article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified exhibition article in storage.
     *
     * @param UpdateExhibitionRequest $request
     * @param News $news
     * @return JsonResponse
     */
    public function update(UpdateExhibitionRequest $request, Exhibition $exhibition): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                // Altes Bild löschen, falls vorhanden
                if ($news->image && Storage::disk('s3')->exists($news->image)) {
                    Storage::disk('s3')->delete($news->image);
                }

                // Neues Bild hochladen
                $path = $request->file('image')->store('news', 's3');
                $data['image'] = $path;
            }

            $exhibition->update($data);

            $exhibition->save();

            return response()->json(new ExhibitionResource($news), 200);
        } catch (Throwable $e) {
            Log::error('Failed to update exhibitions article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update exhibition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified exhibitions article from storage.
     *
     * @param Exhibition $exhibition
     * @return JsonResponse
     */
    public function destroy(Exhibition $exhibition): JsonResponse
    {
        try {
            if ($news->image && Storage::disk('s3')->exists($news->image)) {
                Storage::disk('s3')->delete($news->image);
            }

            $exhibition->delete();

            return response()->json([
                'message' => 'Exhibition deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete following exhibition article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete specified exhibition article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
