<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Port;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_admins'   => User::where('role', 'admin')->count(),
            'total_ports'    => Port::count(),
            'total_articles' => Article::count(),
        ];

        $latestArticles = Article::with('author')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('admin.index', compact('stats', 'latestArticles'));
    }
}