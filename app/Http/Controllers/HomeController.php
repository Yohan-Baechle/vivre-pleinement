<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Video;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home.index', [
            'articles' => Post::query()
                ->published()
                ->with(['categories', 'media'])
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(['id', 'slug', 'title', 'excerpt', 'published_at', 'reading_time_minutes']),
            'videos' => Video::query()
                ->published()
                ->with('categories')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
