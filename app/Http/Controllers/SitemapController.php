<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Clé distincte de l'ancienne `sitemap.videos`, qui contenait une
     * collection de modèles : au déploiement, l'entrée héritée ne doit pas être
     * relue comme du XML. Elle expire seule en une heure.
     */
    public const VIDEOS_CACHE_KEY = 'sitemap.videos.xml';

    /**
     * Pages fixes du site, avec leur fréquence et priorité de crawl.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const STATIC_PAGES = [
        'home' => ['weekly', '1.0'],
        'book.show' => ['weekly', '0.95'],
        'booking.index' => ['weekly', '0.9'],
        'blog.index' => ['daily', '0.9'],
        'courses.index' => ['weekly', '0.9'],
        'therapie-act' => ['monthly', '0.85'],
        'about' => ['monthly', '0.7'],
        'contact' => ['monthly', '0.6'],
        'legal.mentions' => ['yearly', '0.2'],
        'legal.privacy' => ['yearly', '0.2'],
        'legal.cookies' => ['yearly', '0.2'],
        'legal.cgv' => ['yearly', '0.2'],
    ];

    public function index(): Response
    {
        $urls = Cache::remember('sitemap.urls', now()->addHour(), $this->buildUrls(...));

        return response()
            ->view('sitemap', ['urls' => collect($urls)])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * @return list<array<string, string|null>>
     */
    private function buildUrls(): array
    {
        return [
            ...$this->staticEntries(),
            ...$this->entries(
                Post::query()->indexable()->orderByDesc('updated_at')->get(['slug', 'updated_at']),
                fn (Post $post): string => route('blog.show', $post),
                'monthly',
                '0.8',
            ),
            ...$this->entries(
                Category::query()->get(['slug', 'updated_at']),
                fn (Category $category): string => route('blog.category', $category->slug),
                'weekly',
                '0.6',
            ),
            ...$this->entries(
                Tag::query()->get(['slug', 'updated_at']),
                fn (Tag $tag): string => route('blog.tag', $tag->slug),
                'weekly',
                '0.4',
            ),
            ...$this->entries(
                Video::query()->indexable()->orderByDesc('updated_at')->get(['slug', 'updated_at']),
                fn (Video $video): string => route('videos.show', $video),
                'monthly',
                '0.7',
            ),
            ['loc' => route('videos.index'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ...$this->entries(
                Course::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']),
                fn (Course $course): string => route('courses.show', $course),
                'weekly',
                '0.85',
            ),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function staticEntries(): array
    {
        $entries = [];

        foreach (self::STATIC_PAGES as $name => [$changefreq, $priority]) {
            /**
             * Sans formation publiée, l'index des formations est une page vide :
             * l'annoncer à Google dessert le reste du site. Elle revient au
             * sitemap dès la première publication, l'observateur vidant ce cache.
             */
            if ($name === 'courses.index' && ! Course::hasPublished()) {
                continue;
            }

            $entries[] = ['loc' => route($name), 'changefreq' => $changefreq, 'priority' => $priority];
        }

        return $entries;
    }

    /**
     * Transforme une collection de modèles en entrées de sitemap. `lastmod`
     * vient de `updated_at` ; la vue omet la balise lorsqu'il est absent.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Collection<int, TModel>  $models
     * @param  callable(TModel): string  $loc
     * @return list<array<string, string|null>>
     */
    private function entries(Collection $models, callable $loc, string $changefreq, string $priority): array
    {
        return $models->map(fn ($model): array => [
            'loc' => $loc($model),
            'lastmod' => $model->updated_at?->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ])->all();
    }

    /**
     * Le cache porte sur le XML rendu, pas sur les modèles.
     *
     * Mettre des modèles Eloquent en cache les fait traverser un déploiement
     * sous forme sérialisée : une colonne ajoutée ou retirée entre-temps donne
     * un objet incomplet, qu'il fallait jusqu'ici rattraper par une
     * revérification défensive à la lecture. Une chaîne de caractères n'a pas
     * ce problème, et le rendu Blade est économisé au passage.
     */
    public function videos(): Response
    {
        $xml = Cache::remember(
            self::VIDEOS_CACHE_KEY,
            now()->addHour(),
            fn (): string => view('sitemap-videos', ['videos' => $this->videoSitemapQuery()])->render(),
        );

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Fichier de découverte pour les crawlers IA (spécification llmstxt.org).
     */
    public function llms(): Response
    {
        $text = Cache::remember('llms.txt.rendered', now()->addHour(), fn (): string => view('llms', [
            'categories' => Category::query()->orderBy('name')->get(['name', 'slug', 'description']),
            'pillarPosts' => Post::query()
                ->published()
                ->whereIn('slug', [
                    'trouble-anxieux-generalise', 'ruminations', 'phobie-sociale',
                    'toc-troubles-obsessionnels-compulsifs', 'angoisse-matinale', 'burn-out',
                ])
                ->get(['id', 'slug', 'title']),
        ])->render());

        return response($text)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * @return Collection<int, Video>
     */
    private function videoSitemapQuery(): Collection
    {
        return Video::query()
            ->indexable()
            ->orderByDesc('published_at')
            ->get([
                'id', 'slug', 'title', 'youtube_id', 'thumbnail_url',
                'seo_description', 'summary', 'description',
                'duration_seconds', 'published_at', 'view_count',
            ]);
    }
}
