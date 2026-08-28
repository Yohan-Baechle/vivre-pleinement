<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Video;
use App\Support\BookOffers;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(BookOffers $offers): View
    {
        return view('home.index', [
            'bookOffer' => $offers->find(BookOffers::SOLO),
            'articles' => Post::query()
                ->published()
                ->with(['categories', 'media'])
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(['id', 'slug', 'title', 'excerpt', 'published_at', 'updated_at', 'reading_time_minutes']),
            'videos' => Video::query()
                ->published()
                ->with('categories')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
