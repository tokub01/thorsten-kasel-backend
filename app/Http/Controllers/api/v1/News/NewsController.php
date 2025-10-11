<?php

namespace App\Http\Controllers\api\v1\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\News\StoreNewsRequest;
use App\Http\Requests\api\v1\News\UpdateNewsRequest;
use App\Http\Resources\api\v1\News\NewsResource;
use App\Http\Resources\api\v1\News\NewsResourceCollection;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
/**
 * @group News Management
 *
 * Controller for handling news-related operations (CRUD) in API v1.
 */
class NewsController extends Controller
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
            $sort = $request->query('sort', 'newest'); // Default: neueste zuerst

            $news = News::query()
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

            return (new NewsResourceCollection($news))->toResponse($request);

        } catch (Throwable $e) {
            Log::error('Failed to fetch news: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve news.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified news article.
     *
     * @param News $news
     * @return JsonResponse
     */
    public function show(News $news): JsonResponse
    {
        try {
            return response()->json(new NewsResource($news), 200);
        } catch (Throwable $e) {
            Log::error('Failed to fetch news: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the news article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created news article in storage.
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('news', 's3'); // Ordner: news/
                $url = Storage::disk('s3')->url($path);

                $data['image'] = $path;
            }

            $product = News::create($data);

            return (new NewsResourceCollection(News::all()))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to store product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to store news article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified news article in storage.
     *
     * @param StoreNewsRequest $request
     * @param News $news
     * @return JsonResponse
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
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

            $news->update($data);

            $news->save();

            return response()->json(new NewsResource($news), 200);
        } catch (Throwable $e) {
            Log::error('Failed to update news article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update news.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified news article from storage.
     *
     * @param News $product
     * @return JsonResponse
     */
    public function destroy(News $news): JsonResponse
    {
        try {
            if ($news->image && Storage::disk('s3')->exists($news->image)) {
                Storage::disk('s3')->delete($news->image);
            }

            $news->delete();

            return response()->json([
                'message' => 'News deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete following news article: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete specified news article.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
