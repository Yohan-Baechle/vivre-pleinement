<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    private const PER_PAGE = 12;

    private const RELATED_LIMIT = 4;

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:120',
        ]);

        $query = Video::query()
            ->published()
            ->with('categories');

        if ($category = $validated['category'] ?? null) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $category));
        }

        $videos = $query
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categories = Category::query()
            ->whereHas('videos', fn (Builder $q) => $q->published())
            ->withCount(['videos' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('videos.index', [
            'videos' => $videos,
            'categories' => $categories,
            'activeCategory' => $validated['category'] ?? null,
        ]);
    }

    public function show(string $slug): View
    {
        $video = Video::query()
            ->published()
            ->with('categories')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Video::query()
            ->published()
            ->with('categories')
            ->where('id', '!=', $video->id)
            ->when(
                $video->categories->isNotEmpty(),
                fn (Builder $q) => $q->whereHas(
                    'categories',
                    fn (Builder $cq) => $cq->whereIn('categories.id', $video->categories->pluck('id'))
                ),
            )
            ->orderByDesc('published_at')
            ->limit(self::RELATED_LIMIT)
            ->get();

        return view('videos.show', [
            'video' => $video,
            'related' => $related,
        ]);
    }
}
