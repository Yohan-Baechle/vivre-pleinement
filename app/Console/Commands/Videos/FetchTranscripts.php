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
                $text = $this->srtToText($srt);

                if ($text === '') {
                    $this->warn("  ⚠ #{$video->id} : sous-titre vide après nettoyage.");
                    $skipped++;

                    continue;
                }

                // Stocké en texte continu nettoyé ; la reponctuation IA
                // (videos:repunctuate-transcripts) le mettra en paragraphes.
                $video->update(['transcript' => '<p>'.e($text).'</p>']);
                $this->line("  ✓ #{$video->id} « {$video->title} » ({$this->wordCount($text)} mots)");
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
     * Convertit un SRT en texte continu nettoyé, prêt à être reponctué.
     *
     * Les sous-titres (même « standard ») arrivent sans ponctuation fiable :
     * on produit donc un texte propre d'un seul tenant — retrait des index,
     * timecodes, balises, annotations [Musique]/[Applaudissements], doublons
     * consécutifs et espaces superflus. La mise en paragraphes et la
     * ponctuation sont restaurées ensuite par l'étape de reponctuation IA.
     */
    private function srtToText(string $srt): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $srt) ?: [];
        $parts = [];
        $previous = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Index de bloc, timecodes et lignes vides.
            if ($line === '' || ctype_digit($line) || str_contains($line, '-->')) {
                continue;
            }

            $line = trim(strip_tags($line));

            // Annotations non verbales : [Musique], [Applaudissements], (rires)…
            $line = trim((string) preg_replace('/[\[\(][^\]\)]*[\]\)]/u', '', $line));

            if ($line === '' || $line === $previous) {
                continue;
            }

            $parts[] = $line;
            $previous = $line;
        }

        $text = implode(' ', $parts);
        // \s ne couvre pas tous les espaces unicode (insécables, etc.) des
        // sous-titres : on normalise avec le flag /u et la classe \p{Z}.
        $text = (string) preg_replace('/[\s\p{Z}]+/u', ' ', $text);

        return trim($text);
    }

    private function wordCount(string $text): int
    {
        return Str::wordCount(strip_tags($text));
    }
}
