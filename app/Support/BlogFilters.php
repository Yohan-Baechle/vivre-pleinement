<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Tag;

class BlogFilters
{
    /**
     * Construit la query string en gardant les filtres actuels, et en remplaçant/retirant ceux passés.
     *
     * @param  array<string, mixed>  $current  Filtres actifs (depuis la request)
     * @param  array<string, mixed>  $merge  Clés à modifier (null pour retirer)
     */
    public static function url(string $route, array $current, array $merge = []): string
    {
        $params = array_filter(
            array_merge($current, $merge),
            fn ($v) => $v !== null && $v !== '' && $v !== false,
        );

        return route($route, $params);
    }

    /**
     * Retourne les chips à afficher pour les filtres actifs.
     *
     * @param  array<string, mixed>  $filters
     * @param  iterable<Category>  $categories
     * @param  iterable<Tag>  $tags
     * @return array<int, array{label: string, key: string}>
     */
    public static function activeChips(array $filters, iterable $categories, iterable $tags): array
    {
        $chips = [];

        if (! empty($filters['q'])) {
            $chips[] = ['label' => '« '.$filters['q'].' »', 'key' => 'q'];
        }

        if (! empty($filters['category'])) {
            $cat = collect($categories)->firstWhere('slug', $filters['category']);
            if ($cat) {
                $chips[] = ['label' => $cat->name, 'key' => 'category'];
            }
        }

        if (! empty($filters['tag'])) {
            $tag = collect($tags)->firstWhere('slug', $filters['tag']);
            if ($tag) {
                $chips[] = ['label' => '#'.$tag->name, 'key' => 'tag'];
            }
        }

        return $chips;
    }
}
