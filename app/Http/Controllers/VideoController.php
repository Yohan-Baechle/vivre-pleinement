<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use App\Support\VideoArticleMatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    private const PER_PAGE = 12;

    private const RELATED_LIMIT = 4;

    /**
     * Page liste des vidéos. Le listing interactif (recherche, filtres,
     * pagination) est géré par le composant Livewire VideoSearch ; le contrôleur
     * ne fournit que les métadonnées SEO (titre, catégories, JSON-LD ItemList).
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:120',
            'q' => 'nullable|string|max:120',
        ]);

        $categories = Category::query()
            ->whereHas('videos', fn (Builder $q) => $q->published())
            ->orderBy('name')
            ->get();

        // Pour le JSON-LD ItemList de la page canonique uniquement.
        $topVideos = Video::query()
            ->published()
            ->orderByDesc('published_at')
            ->limit(self::PER_PAGE)
            ->get(['id', 'slug', 'title']);

        return view('videos.index', [
            'categories' => $categories,
            'topVideos' => $topVideos,
            'activeCategory' => $validated['category'] ?? null,
            'activeSearch' => $validated['q'] ?? null,
        ]);
    }

    public function show(string $slug): View
    {
        $video = Video::query()
            ->published()
            ->with(['categories', 'relatedPost' => fn ($q) => $q->published()->with('media')])
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
            'relatedPost' => VideoArticleMatcher::postForVideo($video),
        ]);
    }
}
