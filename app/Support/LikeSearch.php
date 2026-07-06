<?php

namespace App\Support;

/**
 * Prépare un terme utilisateur pour une clause LIKE : échappe les jokers SQL
 * (% et _) avant de l'entourer des jokers de recherche, pour qu'une recherche
 * sur "50%" ne soit pas interprétée comme un joker SQL.
 */
class LikeSearch
{
    public static function wrap(string $term): string
    {
        return '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
    }
}
