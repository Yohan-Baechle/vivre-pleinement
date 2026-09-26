<?php

namespace App\Console\Commands\Videos;

use App\Models\Video;
use App\Support\TranscriptChunks;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Réimporte les transcriptions reponctuées (morceaux en HTML) et les
 * recolle dans le champ transcript de chaque vidéo.
 */
#[Signature('videos:import-transcripts
    {path : Fichier JSON reponctué à importer}
    {--dry-run : Affiche sans écrire}')]
#[Description('Réimporte les transcriptions reponctuées (paragraphes HTML).')]
class ImportTranscripts extends Command
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
        $updated = 0;
        $skipped = 0;

        foreach ($payload['videos'] as $row) {
            $video = isset($row['id']) ? Video::find($row['id']) : null;

            if (! $video) {
                $skipped++;

                continue;
            }

            $html = TranscriptChunks::assemble($row['chunks'] ?? []);

            if ($html === '') {
                $skipped++;

                continue;
            }

            if (! $dryRun) {
                $video->update([
                    'transcript' => $html,
                    'transcript_formatted_at' => now(),
                ]);
            }

            $this->line("  ✓ #{$video->id} « {$video->title} » ({$this->wordCount($html)} mots, ".substr_count($html, '<p>').' paragraphes)');
            $updated++;
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}{$updated} transcription(s) importée(s), {$skipped} ignorée(s).");

        return self::SUCCESS;
    }

    private function wordCount(string $html): int
    {
        return Str::wordCount(strip_tags($html));
    }
}
