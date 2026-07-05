<?php

namespace App\Support;

/**
 * Transforme un contenu HTML (saisie visiteur ou import WordPress) en texte
 * brut propre : entités décodées, paragraphes et sauts de ligne préservés,
 * balises supprimées.
 */
class CommentSanitizer
{
    public static function clean(string $content): string
    {
        $text = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('#</p>\s*<p[^>]*>#i', "\n\n", $text);
        $text = preg_replace('#<br\s*/?>#i', "\n", $text);
        $text = strip_tags($text);

        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}
