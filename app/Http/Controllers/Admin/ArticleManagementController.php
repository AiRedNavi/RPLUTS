<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Country;
use Illuminate\Http\Request;

class ArticleManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['author', 'country'])->orderByDesc('published_at');

        if ($search = $request->query('q')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $articles = $query->paginate(10)->withQueryString();

        $stats = [
            'total_articles'       => Article::count(),
            'published_this_month' => Article::where('published_at', '>=', now()->startOfMonth())->count(),
        ];

        $countries = Country::orderBy('name')->get(['id', 'name', 'iso_code']);

        return view('admin.articles', compact('articles', 'stats', 'countries'));
    }

    public function store(StoreArticleRequest $request)
    {
        Article::create([
            ...$request->validated(),
            'author_id'    => auth()->id(),
            'published_at' => $request->validated('published_at') ?? now(),
        ]);

        return back()->with('success', 'Artikel berhasil dipublikasikan.');
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return back()->with('success', "Artikel \"{$article->title}\" berhasil diperbarui.");
    }

    public function destroy(Article $article)
    {
        $title = $article->title;
        $article->delete();

        return back()->with('success', "Artikel \"{$title}\" berhasil dihapus.");
    }
}