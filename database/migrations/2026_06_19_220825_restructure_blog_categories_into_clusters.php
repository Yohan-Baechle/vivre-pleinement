<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;

/**
 * Restructure les catégories du blog en 9 clusters thématiques cohérents.
 *
 * Chaque cluster devient une catégorie unique : un article appartient à un
 * seul cluster, ce qui en fait la source de vérité du maillage interne
 * (articles similaires + page pilier). Remplace l'ancien mécanisme qui
 * reposait sur des tags de cluster et une liste de slugs codée en dur.
 */
return new class extends Migration
{
    /**
     * Cluster => [nom, pilier (slug), liste des slugs d'articles].
     *
     * @var array<string, array{name: string, pillar: string, posts: list<string>}>
     */
    private const CLUSTERS = [
        'blessures-emotionnelles-et-traumatismes' => [
            'name' => 'Blessures émotionnelles & traumatismes',
            'pillar' => 'blessure-de-rejet',
            'posts' => [
                'blessure-dabandon', 'blessure-de-rejet', 'blessure-de-trahison', 'blessure-dinjustice',
                'soigner-blessure-dinjustice', 'guerir-blessure-abandon',
                'comment-soigner-la-blessure-de-rejet-8-conseils-utiles', 'comment-soigner-la-blessure-de-trahison',
                'traumatisme',
            ],
        ],
        'phobies' => [
            'name' => 'Phobies',
            'pillar' => 'phobie-sociale',
            'posts' => [
                'agoraphobie', 'aquaphobie', 'phobie-de-lavion', 'phobie-de-conduire',
                'phobie-changement', 'phobie-sociale', 'ergophobie-peur-du-travail', 'thanatophobie',
            ],
        ],
        'anxiete-et-angoisses' => [
            'name' => 'Anxiété & angoisses',
            'pillar' => 'angoisse-stress-anxiete',
            'posts' => [
                'trouble-anxieux-generalise', 'angoisse-stress-anxiete', 'cause-des-troubles-anxieux',
                'besoin-rassure', 'depersonnalisation-et-derealisation', 'hypocondrie', 'cardiophobie',
                'emetophobie', 'culpabilite', 'le-syndrome-du-dimanche-conseils-pour-vivre-au-mieux-ce-moment',
                'deprime-post-vacances', 'zenspire', 'susceptibilite', 'perfectionnisme',
            ],
        ],
        'toc-et-pensees-intrusives' => [
            'name' => 'TOC & pensées intrusives',
            'pillar' => 'toc-troubles-obsessionnels-compulsifs',
            'posts' => [
                'toc-troubles-obsessionnels-compulsifs', 'les-phobies-dimpulsion',
                'controler-ses-pensees-intrusives-ces-strategies-maintiennent-le-mal-etre', 'ruminations',
            ],
        ],
        'estime-et-confiance-en-soi' => [
            'name' => 'Estime & confiance en soi',
            'pillar' => 'confiance-et-estime-de-soi',
            'posts' => [
                'confiance-et-estime-de-soi', 'affirmation-de-soi', 'se-comparer-aux-autres',
                'jugement-des-autres', 'normes-sociales', 'etre-authentique', 'etre-casanier',
            ],
        ],
        'emotions-difficiles' => [
            'name' => 'Émotions difficiles',
            'pillar' => 'colere',
            'posts' => [
                'colere', 'rancune-et-rancoeur', 'hypersensibilite', 'hypersensibilite-au-travail', 'je-suis-jaloux',
            ],
        ],
        'pleine-conscience-et-lacher-prise' => [
            'name' => 'Pleine conscience & lâcher-prise',
            'pillar' => 'vivre-linstant-present',
            'posts' => [
                'vivre-linstant-present', 'lacher-prise-et-acceptation', 'respiration', 'accords-tolteques',
                'croyances', 'ne-rien-attendre-des-autres', 'symbolique-des-reves', 'effet-miroir',
            ],
        ],
        'sommeil-corps-et-energie' => [
            'name' => 'Sommeil, corps & énergie',
            'pillar' => 'insomnie',
            'posts' => [
                'insomnie', 'angoisses-nocturnes', 'angoisse-matinale', 'fatigue-mentale',
                'reequilibrage-alimentaire', 'burn-out',
            ],
        ],
        'comprendre-et-se-soigner' => [
            'name' => 'Comprendre & se soigner',
            'pillar' => 'quel-est-le-but-de-la-vie',
            'posts' => [
                'quel-est-le-but-de-la-vie', 'devenir-responsable', 'addictions', 'troubles-de-la-personnalite',
                'psychotherapie-et-psychanalyse', 'antidepresseurs-et-anxiolytiques', 'le-mal-a-dit',
            ],
        ],
    ];

    /**
     * Tags de cluster créés par l'ancien mécanisme, désormais inutiles.
     *
     * @var list<string>
     */
    private const OBSOLETE_CLUSTER_TAGS = [
        'blessures-de-lame', 'phobies', 'anxiete-et-angoisses', 'toc-et-pensees-intrusives',
        'estime-et-confiance-en-soi', 'emotions-difficiles', 'pleine-conscience-et-lacher-prise',
        'bien-etre-et-sens-de-la-vie',
    ];

    public function up(): void
    {
        foreach (self::CLUSTERS as $slug => $cluster) {
            $category = Category::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $cluster['name']],
            );

            if ($category->name !== $cluster['name']) {
                $category->update(['name' => $cluster['name']]);
            }

            $postIds = Post::query()
                ->whereIn('slug', $cluster['posts'])
                ->pluck('id', 'slug');

            foreach ($postIds as $postId) {
                Post::query()->find($postId)?->categories()->sync([$category->id]);
            }

            $pillarId = $postIds[$cluster['pillar']] ?? null;
            if ($pillarId) {
                $category->update(['pillar_post_id' => $pillarId]);
            }
        }

        Category::query()
            ->whereIn('slug', ['tous-les-articles', 'angoisse-et-anxiete', 'developpement-personnel', 'blessures-de-lame'])
            ->whereDoesntHave('posts')
            ->whereDoesntHave('videos')
            ->delete();

        Tag::query()->whereIn('slug', self::OBSOLETE_CLUSTER_TAGS)->delete();
    }

    /**
     * Migration de données non réversible : la structure d'origine (tags de
     * cluster + catégories génériques) ne peut être reconstruite de façon
     * fiable. Restaurer depuis une sauvegarde si besoin.
     */
    public function down(): void
    {
        //
    }
};
