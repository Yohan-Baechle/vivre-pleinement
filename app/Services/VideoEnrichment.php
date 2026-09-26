<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Arr;

/**
 * Applique à une vidéo le contenu éditorial produit par l'étape IA :
 * catégories, intro, résumé, description SEO, points clés et chapitres.
 *
 * Un champ vide n'écrase jamais la valeur existante. Avec $onlyMissing,
 * seuls les champs encore vides de la vidéo sont remplis, et les
 * catégories ne sont posées que si elle n'en a aucune.
 */
class VideoEnrichment
{
    /** Balises autorisées dans l'intro. */
    private const INTRO_ALLOWED_TAGS = '<p><br><em><strong>';

    /**
     * @param  array<string, mixed>  $row
     * @return array{
     *     updated: bool,
     *     categorized: bool,
     *     unknown_categories: list<string>,
     * }
     */
    public function apply(
        Video $video,
        array $row,
        bool $dryRun = false,
        bool $onlyMissing = false,
    ): array {
        $attributes = $this->attributes($row);

        if ($onlyMissing) {
            $attributes = array_filter(
                $attributes,
                fn (string $field): bool => blank($video->{$field}),
                ARRAY_FILTER_USE_KEY,
            );
        }

        if ($attributes !== [] && ! $dryRun) {
            $video->update($attributes);
        }

        $slugs = $onlyMissing && $video->categories()->exists()
            ? []
            : array_values(array_filter(
                (array) ($row['category_slugs'] ?? []),
                fn (mixed $slug): bool => is_string($slug) && $slug !== '',
            ));

        $ids = Category::query()
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug');

        if ($ids->isNotEmpty() && ! $dryRun) {
            $video->categories()->sync($ids->values()->all());
        }

        return [
            'updated' => $attributes !== [],
            'categorized' => $ids->isNotEmpty(),
            'unknown_categories' => array_values(
                array_diff($slugs, $ids->keys()->all()),
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function attributes(array $row): array
    {
        $attributes = [];

        $intro = trim(strip_tags(
            (string) ($row['intro'] ?? ''),
            self::INTRO_ALLOWED_TAGS,
        ));
        if ($intro !== '') {
            $attributes['intro'] = $intro;
        }

        foreach (['summary', 'seo_description'] as $field) {
            $value = trim(strip_tags((string) ($row[$field] ?? '')));
            if ($value !== '') {
                $attributes[$field] = $value;
            }
        }

        $takeaways = $this->takeaways($row['key_takeaways'] ?? []);
        if ($takeaways !== []) {
            $attributes['key_takeaways'] = $takeaways;
        }

        $chapters = $this->chapters($row['chapters'] ?? []);
        if ($chapters !== []) {
            $attributes['chapters'] = $chapters;
        }

        return $attributes;
    }

    /**
     * @return list<array{title: string, content: string}>
     */
    private function takeaways(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            $title = trim(strip_tags((string) Arr::get($item, 'title', '')));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'content' => trim(strip_tags(
                    (string) Arr::get($item, 'content', ''),
                )),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{title: string, start_seconds: int}>
     */
    private function chapters(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            $title = trim(strip_tags((string) Arr::get($item, 'title', '')));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'start_seconds' => (int) Arr::get($item, 'start_seconds', 0),
            ];
        }

        return $out;
    }
}
