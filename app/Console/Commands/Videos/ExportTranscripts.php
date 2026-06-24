<?php

namespace App\Console\Commands\Videos;

use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Exporte les transcriptions brutes (texte continu non ponctué) découpées en
 * morceaux, pour reponctuation par l'étape IA.
 */
#[Signature('videos:export-transcripts
    {path : Fichier JSON à écrire}
    {--video= : Limiter à une seule vidéo (id interne)}
    {--chunk-words=1200 : Taille cible d\'un morceau, en mots}')]
#[Description('Exporte les transcriptions brutes en morceaux pour reponctuation.')]
class ExportTranscripts extends Command
{
    public function handle(): int
    {
        $chunkWords = max(300, (int) $this->option('chunk-words'));

        $query = Video::query()
            ->published()
            ->whereNotNull('transcript')
            ->where('transcript', '!=', '');

        if ($videoId = $this->option('video')) {
            $query->whereKey($videoId);
        }

        $videos = $query->orderByDesc('view_count')->get();

        $payload = ['videos' => $videos->map(fn (Video $v) => [
            'id' => $v->id,
            'title' => $v->title,
            'chunks' => $this->chunk(html_entity_decode(strip_tags($v->transcript)), $chunkWords),
        ])->all()];

        $path = $this->argument('path');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $totalChunks = collect($payload['videos'])->sum(fn ($v) => count($v['chunks']));
        $this->info("{$videos->count()} transcription(s) exportée(s) en {$totalChunks} morceau(x) vers {$path}");

        return self::SUCCESS;
    }

    /**
     * Découpe un texte en morceaux d'environ $chunkWords mots, sans couper un mot.
     *
     * @return list<string>
     */
    private function chunk(string $text, int $chunkWords): array
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return [];
        }

        $chunks = [];
        foreach (array_chunk($words, $chunkWords) as $group) {
            $chunks[] = Str::of(implode(' ', $group))->trim()->value();
        }

        return $chunks;
    }
}
