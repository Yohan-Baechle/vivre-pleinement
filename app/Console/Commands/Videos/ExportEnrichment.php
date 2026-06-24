<?php

namespace App\Console\Commands\Videos;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('videos:export-enrichment
    {path : Chemin du fichier JSON à écrire}
    {--all : Inclure aussi les vidéos déjà enrichies}
    {--limit=0 : Limiter le nombre de vidéos exportées (0 = pas de limite)}
    {--order=views : Tri : views (vues décroissantes) ou recent (publication décroissante)}')]
#[Description('Exporte les vidéos vers un fichier JSON à enrichir (catégorie, intro, summary, SEO, key takeaways).')]
class ExportEnrichment extends Command
{
    public function handle(): int
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (Category $c) => ['slug' => $c->slug, 'name' => $c->name])
            ->all();

        $query = Video::query()->published();

        if (! $this->option('all')) {
            $query->whereNull('summary')->whereNull('intro');
        }

        $query->orderByDesc($this->option('order') === 'recent' ? 'published_at' : 'view_count');

        if (($limit = (int) $this->option('limit')) > 0) {
            $query->limit($limit);
        }

        $videos = $query->with('categories:id,slug')->get();

        $payload = [
            '_instructions' => 'Remplissez category_slugs, intro, summary, seo_description, key_takeaways et chapters pour chaque vidéo. Laissez les autres champs intacts. Réimportez avec videos:import-enrichment.',
            '_available_categories' => $categories,
            'videos' => $videos->map(fn (Video $v) => [
                'id' => $v->id,
                'youtube_id' => $v->youtube_id,
                'title' => $v->title,
                'duration_seconds' => $v->duration_seconds,
                'youtube_description' => $v->description,
                'current_category_slugs' => $v->categories->pluck('slug')->all(),
                // À remplir :
                'category_slugs' => $v->categories->pluck('slug')->all(),
                'intro' => '',
                'summary' => '',
                'seo_description' => '',
                'key_takeaways' => [],
                'chapters' => [],
            ])->all(),
        ];

        $path = $this->argument('path');
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info(sprintf('%d vidéo(s) exportée(s) vers %s', $videos->count(), $path));
        $this->line('Taille : '.Str::of(number_format(filesize($path) / 1024, 0))->append(' Ko'));

        return self::SUCCESS;
    }
}
