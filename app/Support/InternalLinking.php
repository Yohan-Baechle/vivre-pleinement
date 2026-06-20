<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Maillage interne du blog.
 *
 * La catégorie d'un article fait office de cluster thématique : c'est la
 * source de vérité unique. Les articles similaires sont les autres articles
 * du même cluster (affinés par tags partagés), et la « page pilier » est
 * l'article de référence désigné sur la catégorie (`pillar_post_id`).
 *
 * Les résultats sont mis en cache par article et invalidés par les observers
 * de Post et de Category dès qu'un contenu ou un pilier change.
 */
class InternalLinking
{
    private const SIMILAR_LIMIT = 3;

    private const CACHE_TTL_MINUTES = 1440;

    /**
     * Articles similaires : même cluster (catégorie) en priorité, classés par
     * nombre de tags partagés puis par fraîcheur. Complète avec les articles
     * récents de la catégorie pour toujours remplir le bloc.
     *
     * Le cache ne stocke que les IDs ordonnés (des scalaires) : sérialiser des
     * modèles Eloquent dans le cache est fragile (classes incomplètes, données
     * périmées). On recharge les articles frais à la lecture, en préservant
     * l'ordre de pertinence calculé.
     *
     * @return Collection<int, Post>
     */
    public static function similar(Post $post): Collection
    {
        $ids = Cache::remember(
            self::cacheKey('similar', $post),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => self::computeSimilar($post)->modelKeys(),
        );

        if ($ids === []) {
            return new Collection;
        }

        return Post::query()
            ->published()
            ->with(['categories', 'media'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Post $p) => array_search($p->id, $ids, true))
            ->values();
    }

    /**
     * Page pilier du cluster de l'article (bloc « Pour aller plus loin »).
     * Null si l'article est lui-même le pilier, si le pilier n'est pas publié,
     * ou si la catégorie n'a pas de pilier défini.
     */
    public static function pillar(Post $post): ?Post
    {
        $pillarId = Cache::remember(
            self::cacheKey('pillar', $post),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => self::computePillarId($post),
        );

        if ($pillarId === null) {
            return null;
        }

        return Post::query()->published()->with(['categories', 'media'])->find($pillarId);
    }

    /**
     * Vide le cache de maillage de tous les articles publiés du/des cluster(s)
     * de l'article donné (lui inclus). Appelé quand un article est édité ou
     * qu'un pilier change, car cela affecte le bloc « similaires » de tous les
     * articles voisins, pas seulement de celui qui a changé.
     */
    public static function flushCluster(Post $post): void
    {
        $categoryIds = $post->categories()->pluck('categories.id');

        $postIds = Post::query()
            ->when(
                $categoryIds->isNotEmpty(),
                fn ($q) => $q->whereHas('categories', fn ($cq) => $cq->whereIn('categories.id', $categoryIds)),
                fn ($q) => $q->whereKey($post->id),
            )
            ->pluck('id')
            ->push($post->id)
            ->unique();

        foreach ($postIds as $id) {
            Cache::forget("blog.linking.similar.{$id}");
            Cache::forget("blog.linking.pillar.{$id}");
        }
    }

    /**
     * Vide le cache de maillage de tous les articles publiés d'une catégorie.
     */
    public static function flushCategory(Category $category): void
    {
        $postIds = $category->posts()->pluck('posts.id');

        foreach ($postIds as $id) {
            Cache::forget("blog.linking.similar.{$id}");
            Cache::forget("blog.linking.pillar.{$id}");
        }
    }

    /**
     * @return Collection<int, Post>
     */
    private static function computeSimilar(Post $post): Collection
    {
        $categoryIds = $post->categories->pluck('id');
        $tagIds = $post->tags->pluck('id');

        $base = fn () => Post::query()
            ->published()
            ->where('id', '!=', $post->id);

        $byCluster = new Collection;
        if ($categoryIds->isNotEmpty()) {
            $byCluster = $base()
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
                ->withCount(['tags as shared_tags' => fn ($q) => $q->whereIn('tags.id', $tagIds)])
                ->orderByDesc('shared_tags')
                ->orderByDesc('published_at')
                ->limit(self::SIMILAR_LIMIT)
                ->get();
        }

        if ($byCluster->count() >= self::SIMILAR_LIMIT) {
            return $byCluster;
        }

        $fillers = $base()
            ->whereNotIn('id', $byCluster->pluck('id'))
            ->orderByDesc('published_at')
            ->limit(self::SIMILAR_LIMIT - $byCluster->count())
            ->get();

        return $byCluster->merge($fillers);
    }

    private static function computePillarId(Post $post): ?int
    {
        $pillar = Category::query()
            ->whereIn('categories.id', $post->categories->pluck('id'))
            ->whereNotNull('pillar_post_id')
            ->first();

        if ($pillar === null || $pillar->pillar_post_id === $post->id) {
            return null;
        }

        return $pillar->pillar_post_id;
    }

    private static function cacheKey(string $kind, Post $post): string
    {
        return "blog.linking.{$kind}.{$post->id}";
    }
}
