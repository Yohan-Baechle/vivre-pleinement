<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\InternalLinking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'category' => 'nullable|string|max:120',
            'tag' => 'nullable|string|max:120',
            'sort' => 'nullable|in:recent,oldest',
        ]);

        $hasFilters = collect(['q', 'category', 'tag'])->some(fn ($k) => ! empty($validated[$k] ?? null));
        $featured = $this->featuredPost($request, $validated, $hasFilters);

        $query = Post::query()
            ->published()
            ->with(['categories', 'tags', 'media'])
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id));

        $this->applyFilters($query, $validated);

        $posts = $query
            ->orderBy('published_at', ($validated['sort'] ?? 'recent') === 'oldest' ? 'asc' : 'desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'featured' => $featured,
            'categories' => $this->sidebarCategories(),
            'popularTags' => $this->popularTags(),
            'allTags' => Tag::query()->get(),
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
     * Le dernier article publié, mis en avant uniquement sur la première page non filtrée et triée par défaut.
     *
     * @param  array<string, mixed>  $filters
     */
    private function featuredPost(Request $request, array $filters, bool $hasFilters): ?Post
    {
        $isFirstPage = ! $request->filled('page') || (int) $request->get('page') === 1;

        if ($hasFilters || ! $isFirstPage || ($filters['sort'] ?? 'recent') !== 'recent') {
            return null;
        }

        return Post::query()
            ->published()
            ->with(['categories', 'tags', 'media'])
            ->orderByDesc('published_at')
            ->first();
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

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($search = $filters['q'] ?? null) {
            $query->where(function (Builder $q) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            });
        }

        if ($category = $filters['category'] ?? null) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $category));
        }

        if ($tag = $filters['tag'] ?? null) {
            $query->whereHas('tags', fn (Builder $q) => $q->where('slug', $tag));
        }
    }
}
