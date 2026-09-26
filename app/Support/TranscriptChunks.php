<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Découpe une transcription brute en morceaux pour l'étape de reponctuation
 * IA, puis recolle les morceaux reponctués en un HTML propre.
 */
class TranscriptChunks
{
    /** Balises autorisées dans une transcription reponctuée. */
    public const ALLOWED_TAGS = '<p><br><em><strong>';

    /**
     * Découpe un texte en morceaux d'environ $chunkWords mots, sans couper un
     * mot.
     *
     * @return list<string>
     */
    public static function split(string $html, int $chunkWords): array
    {
        $groups = array_chunk(self::words($html), max(1, $chunkWords));

        $chunks = [];
        foreach ($groups as $group) {
            $chunks[] = Str::of(implode(' ', $group))->trim()->value();
        }

        return $chunks;
    }

    public static function wordCount(string $html): int
    {
        return count(self::words($html));
    }

    /**
     * Texte lisible d'une transcription HTML, un paragraphe par bloc.
     */
    public static function plainText(string $html): string
    {
        $paragraphs = preg_split('/<\/p>|<br\s*\/?>/i', $html) ?: [];

        return collect($paragraphs)
            ->map(fn (string $paragraph): string => trim(
                html_entity_decode(strip_tags($paragraph)),
            ))
            ->filter()
            ->implode("\n\n");
    }

    /**
     * Recolle les morceaux reponctués en un seul HTML propre.
     */
    public static function assemble(mixed $chunks): string
    {
        if (! is_array($chunks)) {
            return '';
        }

        $clean = [];
        foreach ($chunks as $chunk) {
            $chunk = trim(strip_tags((string) $chunk, self::ALLOWED_TAGS));
            if ($chunk !== '') {
                $clean[] = $chunk;
            }
        }

        return Str::of(implode("\n", $clean))->trim()->value();
    }

    /**
     * @return list<string>
     */
    private static function words(string $html): array
    {
        $text = trim(html_entity_decode(
            (string) preg_replace('/<[^>]*>/', ' ', $html),
        ));

        return preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
