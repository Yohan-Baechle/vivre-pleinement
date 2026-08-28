<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Http\Requests\PostIndexFormRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\InternalLinking;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    /**
     * Clé distincte de l'ancienne `blog.rss.posts`, qui contenait des
     * modèles : au déploiement, l'entrée héritée ne doit pas être
     * relue comme du XML. Elle expire seule en trente minutes.
     */
    public const RSS_CACHE_KEY = 'blog.rss.xml';

    /**
     * Page blog. Le listing interactif (recherche, tri, pagination, chips) est
     * géré par le composant Livewire PostSearch ; le contrôleur ne fournit que
     * les métadonnées SEO, la sidebar (catégories/tags en liens indexables) et
     * $previewPosts, un aperçu (featured + premiers articles) pour le JSON-LD
     * de la page canonique non filtrée uniquement — le listing affiché vient
     * de Livewire.
     */
    public function index(PostIndexFormRequest $request): View
    {
        $validated = $request->validated();

        $hasFilters = collect(['q', 'category', 'tag'])->some(fn (string $filter) => ! empty($validated[$filter] ?? null));

        $previewPosts = collect();
        if (! $hasFilters && ($validated['sort'] ?? 'recent') === 'recent') {
            $previewPosts = Post::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(self::PER_PAGE + 1)
                ->get(['id', 'slug', 'title']);
        }

        return view('blog.index', [
            'previewPosts' => $previewPosts,
            'filters' => $validated,
            'hasFilters' => $hasFilters,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->renderPost($post);
    }

    /**
     * Aperçu d'un article non publié, réservé à l'administration : l'URL est
     * signée et expire, et la page est marquée noindex pour ne jamais entrer
     * dans l'index de Google.
     */
    public function preview(Post $post): Response
    {
        return response($this->renderPost($post)->render())
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function renderPost(Post $post): View
    {
        $post->load([
            'categories',
            'tags',
            'media',
            'comments' => fn ($query) => $query->where('status', CommentStatus::Approved)->whereNull('parent_id')->orderBy('posted_at'),
            'comments.replies' => fn ($query) => $query->where('status', CommentStatus::Approved)->orderBy('posted_at'),
        ]);

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
            ->whereHas('categories', fn ($query) => $query->where('categories.id', $category->id))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE, [
                'id', 'slug', 'title', 'excerpt', 'published_at', 'updated_at', 'reading_time_minutes',
            ])
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
            ->whereHas('tags', fn ($query) => $query->where('tags.id', $tag->id))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE, [
                'id', 'slug', 'title', 'excerpt', 'published_at', 'updated_at', 'reading_time_minutes',
            ])
            ->withQueryString();

        return view('blog.taxonomy', [
            'posts' => $posts,
            'taxonomy' => $tag,
            'kind' => 'tag',
        ]);
    }

    /**
     * Le cache porte sur le XML rendu, pas sur les modèles : une
     * collection Eloquent sérialisée survit à un déploiement et se
     * réveille incomplète si le schéma a bougé entre-temps. Le rendu
     * Blade des cinquante articles est économisé au passage.
     */
    public function rss(): Response
    {
        $xml = Cache::remember(
            self::RSS_CACHE_KEY,
            now()->addMinutes(30),
            fn (): string => view('blog.rss', [
                'posts' => Post::query()
                    ->published()
                    ->with(['categories'])
                    ->orderByDesc('published_at')
                    ->limit(50)
                    ->get(),
            ])->render(),
        );

        return response($xml)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800');
    }
}
