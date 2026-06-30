<?php

namespace App\Console\Commands\Videos;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

#[Signature('videos:import-enrichment
    {path : Chemin du fichier JSON enrichi à importer}
    {--dry-run : Affiche ce qui serait modifié sans rien écrire}')]
#[Description('Importe un fichier JSON enrichi : catégories, intro, summary, SEO description, key takeaways, chapitres.')]
class ImportEnrichment extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("Fichier introuvable : {$path}");

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['videos']) || ! is_array($payload['videos'])) {
            $this->error('JSON invalide : clé "videos" manquante.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $categoryIdsBySlug = Category::query()->pluck('id', 'slug');

        $updated = 0;
        $categorized = 0;
        $skipped = 0;
        $warnings = [];

        foreach ($payload['videos'] as $row) {
            $video = isset($row['id']) ? Video::find($row['id']) : null;

            if (! $video) {
                $warnings[] = 'Vidéo introuvable (id '.($row['id'] ?? '?').')';
                $skipped++;

                continue;
            }

            $attributes = [];

            foreach (['intro', 'summary', 'seo_description'] as $field) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '') {
                    $attributes[$field] = $value;
                }
            }

            $takeaways = $this->normalizeTakeaways($row['key_takeaways'] ?? []);
            if ($takeaways !== []) {
                $attributes['key_takeaways'] = $takeaways;
            }

            $chapters = $this->normalizeChapters($row['chapters'] ?? []);
            if ($chapters !== []) {
                $attributes['chapters'] = $chapters;
            }

            if ($attributes !== []) {
                if (! $dryRun) {
                    $video->update($attributes);
                }
                $updated++;
            }

            $slugs = array_values(array_filter((array) ($row['category_slugs'] ?? [])));
            if ($slugs !== []) {
                $ids = $categoryIdsBySlug->only($slugs);

                $unknown = array_diff($slugs, $ids->keys()->all());
                foreach ($unknown as $slug) {
                    $warnings[] = "Catégorie inconnue ignorée : « {$slug} » (vidéo {$video->id})";
                }

                if ($ids->isNotEmpty()) {
                    if (! $dryRun) {
                        $video->categories()->sync($ids->values()->all());
                    }
                    $categorized++;
                }
            }
        }

        foreach ($warnings as $warning) {
            $this->warn('  ⚠ '.$warning);
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info(sprintf(
            '%s%d vidéo(s) enrichie(s), %d recatégorisée(s), %d ignorée(s).',
            $prefix,
            $updated,
            $categorized,
            $skipped,
        ));

        return self::SUCCESS;
    }

    /**
     * @return list<array{title: string, content: string}>
     */
    private function normalizeTakeaways(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            $title = trim((string) Arr::get($item, 'title', ''));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'content' => trim((string) Arr::get($item, 'content', '')),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{title: string, start_seconds: int}>
     */
    private function normalizeChapters(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            $title = trim((string) Arr::get($item, 'title', ''));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'start_seconds' => (int) Arr::get($item, 'start_seconds', 0),
            ];
        }

        return $out;
    }
}
