<?php

namespace App\Console\Commands\Videos;

use App\Models\Video;
use App\Services\VideoEnrichment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('videos:import-enrichment
    {path : Chemin du fichier JSON enrichi à importer}
    {--dry-run : Affiche ce qui serait modifié sans rien écrire}')]
#[Description('Importe un fichier JSON enrichi : catégories, intro, summary, SEO description, key takeaways, chapitres.')]
class ImportEnrichment extends Command
{
    public function handle(VideoEnrichment $enrichment): int
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

            $result = $enrichment->apply($video, $row, $dryRun);

            $updated += (int) $result['updated'];
            $categorized += (int) $result['categorized'];

            foreach ($result['unknown_categories'] as $slug) {
                $warnings[] = "Catégorie inconnue ignorée : « {$slug} » "
                    ."(vidéo {$video->id})";
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
}
