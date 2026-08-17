<?php

namespace App\Support;

class VideoEmbed
{
    /**
     * Extrait le fournisseur et l'identifiant d'une URL YouTube/Vimeo collée,
     * ou conserve la valeur telle quelle si c'est déjà un identifiant brut.
     *
     * @return array{provider: string, id: ?string}
     */
    public static function parse(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return ['provider' => 'youtube', 'id' => null];
        }

        if (preg_match('#(?:youtube\.com/.*[?&]v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})#i', $value, $m)) {
            return ['provider' => 'youtube', 'id' => $m[1]];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $value, $m)) {
            return ['provider' => 'vimeo', 'id' => $m[1]];
        }

        return ['provider' => ctype_digit($value) ? 'vimeo' : 'youtube', 'id' => $value];
    }
}
