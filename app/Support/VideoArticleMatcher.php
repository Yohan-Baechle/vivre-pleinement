<?php

namespace App\Support;

use App\Models\Post;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Apparie une vidéo et un article par pertinence lexicale de leurs titres.
 *
 * Sert de repli quand aucun lien explicite (related_post_id) n'existe : on
 * compare les mots significatifs des titres plutôt que de retomber sur le
 * contenu le plus populaire de la catégorie, qui serait souvent hors-sujet.
 *
 * Principe directeur : mieux vaut ne rien proposer qu'un contenu non
 * pertinent. Un score minimal est donc exigé (SCORE_THRESHOLD).
 *
 * Le résultat est mis en cache par contenu (ID scalaire uniquement, 0 pour
 * « aucune correspondance »), invalidé par les observers Post et Video : le
 * scoring parcourait sinon tous les contenus de la catégorie à chaque page.
 */
class VideoArticleMatcher
{
    /**
     * Nombre minimal de mots significatifs partagés pour considérer deux
     * titres comme thématiquement proches.
     */
    private const SCORE_THRESHOLD = 1;

    private const CACHE_TTL_MINUTES = 1440;

    private const NO_MATCH = 0;

    /**
     * Mots vides et termes éditoriaux trop génériques pour porter du sens
     * thématique (ils apparaissent dans des dizaines de titres).
     *
     * @var list<string>
     */
    private const STOPWORDS = [
        'le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'd', 'et', 'ou', 'a', 'à',
        'entre', 'vers', 'chez', 'depuis', 'apres', 'après', 'avant', 'pendant',
        'au', 'aux', 'en', 'dans', 'pour', 'par', 'sur', 'sous', 'avec', 'sans', 'ce',
        'cette', 'ces', 'son', 'sa', 'ses', 'mon', 'ma', 'mes', 'que', 'qui', 'quoi',
        'est', 'sont', 'se', 'ne', 'pas', 'plus', 'comment', 'pourquoi', 'quel',
        'quelle', 'quelles', 'quels', 'vous', 'nous', 'je', 'tu', 'il', 'elle', 'on',
        'mieux', 'bien', 'enfin', 'vraiment', 'tout', 'tous', 'toute', 'toutes',
        'faire', 'avoir', 'etre', 'être', 'son', 'leur', 'leurs', 'mes',
        'conseils', 'conseil', 'astuces', 'guerir', 'guérir', 'liberer', 'libérer',
        'comprendre', 'soigner', 'vaincre', 'sortir', 'stopper', 'arreter', 'arrêter',
        'experience', 'expérience', 'avis', 'symptomes', 'symptômes', 'caracterisent',
        'reconnaitre', 'reconnaître', 'meilleur', 'moyen', 'cle', 'clé', 'differences',
        'video', 'vidéo', 'chaine', 'chaîne', 'troubles', 'trouble', 'anxieux',
        'anxiete', 'anxiété', 'angoisse', 'angoisses', 'mon', 'suis', 'devenue', 'fait',
        'vie', 'vivre', 'quotidien', 'heureux', 'bonheur', 'bien-etre', 'serenite',
        'sereinement', 'paix', 'point', 'points', 'vue', 'vues', 'differents',
        'personnelles', 'personnel', 'soi', 'meme', 'même', 'autres', 'gens',
        'jour', 'jours', 'chose', 'choses', 'facon', 'façon', 'maniere', 'manière',
        'peur', 'peurs', 'phobie', 'phobies', 'crise', 'crises', 'stress', 'emotion',
        'emotions', 'émotions', 'mal', 'etre', 'mieux', 'libere', 'libérée',
    ];

    /**
     * Meilleure vidéo pour un article : le lien explicite d'abord, sinon la
     * vidéo de la même catégorie dont le titre recouvre le plus celui de
     * l'article. Retourne null si aucune correspondance n'est assez pertinente.
     */
    public static function videoForPost(Post $post): ?Video
    {
        $videoId = Cache::remember(
            "videos.match.post.{$post->id}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => self::computeVideoIdForPost($post) ?? self::NO_MATCH,
        );

        if ($videoId === self::NO_MATCH) {
            return null;
        }

        return Video::query()->published()->find($videoId);
    }

    /**
     * Meilleur article pour une vidéo : le lien explicite d'abord, sinon
     * l'article de la même catégorie dont le titre recouvre le plus celui de
     * la vidéo. Retourne null si aucune correspondance n'est assez pertinente.
     */
    public static function postForVideo(Video $video): ?Post
    {
        $postId = Cache::remember(
            "blog.match.video.{$video->id}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => self::computePostIdForVideo($video) ?? self::NO_MATCH,
        );

        if ($postId === self::NO_MATCH) {
            return null;
        }

        return Post::query()->with(['categories', 'media'])->find($postId);
    }

    /**
     * Vide tout le cache d'appariement. Un contenu créé ou modifié peut changer
     * le meilleur match de n'importe quel contenu voisin, et ses catégories
     * sont synchronisées après l'événement saved : un flush ciblé par catégorie
     * raterait des clés. Les écritures étant rares (admin), le flush large est
     * le compromis sûr.
     */
    public static function flush(): void
    {
        foreach (Post::query()->pluck('id') as $id) {
            Cache::forget("videos.match.post.{$id}");
        }

        foreach (Video::query()->pluck('id') as $id) {
            Cache::forget("blog.match.video.{$id}");
        }
    }

    private static function computeVideoIdForPost(Post $post): ?int
    {
        $explicit = $post->videos()->published()->orderByDesc('view_count')->first(['videos.id']);

        if ($explicit) {
            return $explicit->id;
        }

        $categoryIds = $post->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return null;
        }

        $candidates = Video::query()
            ->published()
            ->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $categoryIds))
            ->get(['id', 'title']);

        return self::bestMatch($post->title, $candidates, fn (Video $v) => $v->title)?->id;
    }

    private static function computePostIdForVideo(Video $video): ?int
    {
        if ($video->related_post_id && $video->relatedPost && $video->relatedPost->published_at) {
            return $video->related_post_id;
        }

        $categoryIds = $video->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return null;
        }

        $candidates = Post::query()
            ->published()
            ->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $categoryIds))
            ->get(['id', 'title']);

        return self::bestMatch($video->title, $candidates, fn (Post $p) => $p->title)?->id;
    }

    /**
     * Sélectionne, parmi les candidats, celui dont le titre partage le plus de
     * mots significatifs avec le titre de référence, au-dessus du seuil.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Collection<int, TModel>  $candidates
     * @param  callable(TModel): string  $titleOf
     * @return TModel|null
     */
    private static function bestMatch(string $referenceTitle, Collection $candidates, callable $titleOf)
    {
        $reference = self::tokens($referenceTitle);

        if ($reference->isEmpty()) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $candidate) {
            $score = self::tokens($titleOf($candidate))->intersect($reference)->count();

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= self::SCORE_THRESHOLD ? $best : null;
    }

    /**
     * Découpe un titre en mots significatifs : minuscules, sans accents, sans
     * mots vides ni termes éditoriaux génériques, et de plus de 2 lettres.
     *
     * @return Collection<int, string>
     */
    private static function tokens(string $title): Collection
    {
        $normalized = Str::of($title)->lower()->ascii()->toString();

        return Str::of($normalized)
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->explode(' ')
            ->map(fn (string $word) => trim($word))
            ->filter(fn (string $word) => Str::length($word) > 2 && ! in_array($word, self::STOPWORDS, true))
            ->unique()
            ->values();
    }
}
