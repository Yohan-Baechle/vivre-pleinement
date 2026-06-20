<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Parse un HTML d'article, ajoute des `id` aux <h2>/<h3> et extrait le sommaire.
 */
class Toc
{
    /**
     * @return array{html: string, items: array<int, array{level: int, id: string, text: string}>}
     */
    public static function build(?string $html): array
    {
        if (blank($html)) {
            return ['html' => '', 'items' => []];
        }

        $items = [];
        $seen = [];

        $out = preg_replace_callback(
            '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
            function ($match) use (&$items, &$seen) {
                $level = (int) $match[1];
                $attrs = $match[2];
                $inner = $match[3];
                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                if ($text === '') {
                    return $match[0];
                }

                $base = Str::slug($text);
                $id = $base;
                $i = 2;
                while (isset($seen[$id])) {
                    $id = $base.'-'.$i++;
                }
                $seen[$id] = true;

                $items[] = ['level' => $level, 'id' => $id, 'text' => $text];

                if (preg_match('/\bid=/i', $attrs)) {
                    return $match[0];
                }

                return sprintf('<h%d id="%s"%s>%s</h%d>', $level, $id, $attrs, $inner, $level);
            },
            $html
        );

        return ['html' => $out ?? $html, 'items' => $items];
    }
}
