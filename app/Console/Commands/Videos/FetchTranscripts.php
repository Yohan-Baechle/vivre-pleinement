<?php

namespace App\Console\Commands\Videos;

use App\Models\Video;
use App\Services\YoutubeCaptions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

/**
 * Récupère et nettoie les sous-titres YouTube pour les stocker comme
 * transcription indexable sur chaque page vidéo.
 */
#[Signature('youtube:fetch-transcripts
    {--video= : Limiter à une seule vidéo (id interne)}
    {--limit=0 : Nombre maximum de vidéos à traiter (0 = toutes)}
    {--force : Re-télécharger même les vidéos ayant déjà une transcription}
    {--language=fr : Langue des sous-titres à récupérer}')]
#[Description('Télécharge les sous-titres YouTube (standard puis ASR) et les stocke comme transcription nettoyée.')]
class FetchTranscripts extends Command
{
    public function handle(): int
    {
        $captions = YoutubeCaptions::fromConfig();

        if (! $captions->isConfigured()) {
            $this->error('OAuth non configuré. Lancez d\'abord : vendor/bin/sail artisan youtube:oauth-setup');

            return self::FAILURE;
        }

        $language = (string) $this->option('language');

        $query = Video::query()->published();

        if ($videoId = $this->option('video')) {
            $query->whereKey($videoId);
        }
        if (! $this->option('force')) {
            $query->where(fn ($q) => $q->whereNull('transcript')->orWhere('transcript', ''));
        }
        if (($limit = (int) $this->option('limit')) > 0) {
            $query->limit($limit);
        }

        $videos = $query->orderByDesc('view_count')->get();

        if ($videos->isEmpty()) {
            $this->info('Aucune vidéo à traiter.');

            return self::SUCCESS;
        }

        $this->info("Traitement de {$videos->count()} vidéo(s)…");
        $fetched = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($videos as $video) {
            try {
                $tracks = $captions->listTracks($video->youtube_id);
                $trackId = $captions->pickBestTrackId($tracks, $language);

                if (! $trackId) {
                    $this->warn("  ⚠ #{$video->id} « {$video->title} » : aucun sous-titre {$language}.");
                    $skipped++;

                    continue;
                }

                $srt = $captions->downloadTrack($trackId);
                $html = $this->srtToHtml($srt);

                if ($html === '') {
                    $this->warn("  ⚠ #{$video->id} : sous-titre vide après nettoyage.");
                    $skipped++;

                    continue;
                }

                $video->update(['transcript' => $html]);
                $this->line("  ✓ #{$video->id} « {$video->title} » ({$this->wordCount($html)} mots)");
                $fetched++;
            } catch (Throwable $e) {
                $this->warn("  ✗ #{$video->id} : ".$e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Terminé : {$fetched} récupérée(s), {$skipped} sans sous-titre, {$failed} en échec.");

        return self::SUCCESS;
    }

    /**
     * Convertit un SRT en HTML lisible : retire timecodes, numéros et balises,
     * fusionne les lignes en phrases puis regroupe en paragraphes.
     */
    private function srtToHtml(string $srt): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $srt) ?: [];
        $textParts = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorer numéros de bloc, timecodes et lignes vides.
            if ($line === '' || ctype_digit($line) || str_contains($line, '-->')) {
                continue;
            }

            // Retirer les balises éventuelles (<i>, <c>, etc.).
            $line = trim(strip_tags($line));

            if ($line !== '') {
                $textParts[] = $line;
            }
        }

        if ($textParts === []) {
            return '';
        }

        // Recoller en un texte continu, en dédupliquant les répétitions
        // consécutives fréquentes dans les sous-titres ASR.
        $previous = null;
        $clean = [];
        foreach ($textParts as $part) {
            if ($part !== $previous) {
                $clean[] = $part;
                $previous = $part;
            }
        }

        $fullText = implode(' ', $clean);
        $fullText = preg_replace('/\s+/', ' ', $fullText) ?? $fullText;

        // Découper en phrases puis regrouper en paragraphes de ~4 phrases.
        $sentences = preg_split('/(?<=[.!?])\s+/', $fullText, -1, PREG_SPLIT_NO_EMPTY) ?: [$fullText];
        $paragraphs = [];
        foreach (array_chunk($sentences, 4) as $chunk) {
            $paragraphs[] = '<p>'.e(trim(implode(' ', $chunk))).'</p>';
        }

        return implode("\n", $paragraphs);
    }

    private function wordCount(string $html): int
    {
        return Str::wordCount(strip_tags($html));
    }
}
