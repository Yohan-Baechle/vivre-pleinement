<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\InternalLinking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    /**
     * Page blog. Le listing interactif (recherche, tri, pagination, chips) est
     * géré par le composant Livewire PostSearch ; le contrôleur ne fournit que
     * les métadonnées SEO, la sidebar (catégories/tags en liens indexables) et
     * l'aperçu pour le JSON-LD de la page canonique.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'category' => 'nullable|string|max:120',
            'tag' => 'nullable|string|max:120',
            'sort' => 'nullable|in:recent,oldest',
        ]);

        $hasFilters = collect(['q', 'category', 'tag'])->some(fn ($k) => ! empty($validated[$k] ?? null));

        // Aperçu (featured + premiers articles) uniquement pour le JSON-LD de la
        // page canonique non filtrée. Le listing affiché vient de Livewire.
        $previewPosts = collect();
        if (! $hasFilters && ($validated['sort'] ?? 'recent') === 'recent') {
            $previewPosts = Post::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(self::PER_PAGE + 1)
                ->get(['id', 'slug', 'title']);
        }

        return view('blog.index', [
            'categories' => $this->sidebarCategories(),
            'popularTags' => $this->popularTags(),
            'previewPosts' => $previewPosts,
            'filters' => $validated,
            'hasFilters' => $hasFilters,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->with([
                'categories',
                'tags',
                'media',
                'comments' => fn ($q) => $q->where('status', CommentStatus::Approved)->whereNull('parent_id')->orderBy('posted_at'),
                'comments.replies' => fn ($q) => $q->where('status', CommentStatus::Approved)->orderBy('posted_at'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', [
            'post' => $post,
            'similar' => InternalLinking::similar($post),
            'pillar' => InternalLinking::pillar($post),
            'relatedVideo' => $post->bestRelatedVideo(),
        ]);
    }

    public function byCategory(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->with(['categories', 'tags', 'media'])
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('blog.taxonomy', [
            'posts' => $posts,
            'taxonomy' => $category,
            'kind' => 'category',
        ]);
    }

    public function byTag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->with(['categories', 'tags', 'media'])
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('blog.taxonomy', [
            'posts' => $posts,
            'taxonomy' => $tag,
            'kind' => 'tag',
        ]);
    }

    public function rss(): Response
    {
        $posts = Cache::remember(
            'blog.rss.posts',
            now()->addMinutes(30),
            fn () => Post::query()
                ->published()
                ->with(['categories'])
                ->orderByDesc('published_at')
                ->limit(50)
                ->get(),
        );

        return response()
            ->view('blog.rss', ['posts' => $posts])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800');
    }

    /**
     * @return Collection<int, Category>
     */
    private function sidebarCategories(): Collection
    {
        return Category::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return SupportCollection<int, Tag>
     */
    private function popularTags(): SupportCollection
    {
        return Tag::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get()
            ->filter(fn ($t) => $t->posts_count > 0);
    }
}
