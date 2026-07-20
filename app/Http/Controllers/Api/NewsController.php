<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsArticleResource;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * GET /api/news
     * GET /api/news?category=logistics
     * GET /api/news?sentiment=negative
     * GET /api/news?country=DEU
     */
    public function index(Request $request): JsonResponse
    {
        $query = NewsArticle::query()->with('country');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($sentiment = $request->query('sentiment')) {
            $query->where('sentiment_label', $sentiment);
        }

         if ($countryId = $request->query('country_id')) {
            $query->where('country_id', $countryId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('summary', 'ILIKE', "%{$search}%");
            });
        }

        $news = $query->latest('published_at')->paginate(15);

        return response()->json([
            'data' => NewsArticleResource::collection($news),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'total' => $news->total(),
            ],
        ]);
    }

    /**
     * GET /api/news/{id}
     */
    public function show(int $id): JsonResponse
    {
        $article = NewsArticle::with('country')->find($id);

        if (! $article) {
            return response()->json(['message' => 'Berita tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new NewsArticleResource($article)]);
    }
}