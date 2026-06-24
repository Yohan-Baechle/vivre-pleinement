<?php

namespace App\Console\Commands\Videos;

use App\Models\Post;
use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('videos:link-related-posts
    {--dry-run : Affiche les associations détectées sans rien écrire}
    {--force : Réassocie même les vidéos ayant déjà un related_post_id}')]
#[Description('Associe chaque vidéo à son article de blog en analysant les liens vivre-pleinement.fr présents dans la description YouTube.')]
class LinkRelatedPosts extends Command
{
    /** Chemins du site qui ne sont pas des articles de blog. */
    private const NON_ARTICLE_SLUGS = [
        'prendre-rendez-vous', 'step', 'landing-page', 'contact', 'blog',
        'videos', 'a-propos', 'mentions-legales', 'boutique',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $postIdsBySlug = Post::query()->pluck('id', 'slug');

        $query = Video::query()->whereNotNull('description');
        if (! $force) {
            $query->whereNull('related_post_id');
        }

        $videos = $query->get();
        $linked = 0;
        $unmatched = 0;

        foreach ($videos as $video) {
            $slug = $this->extractArticleSlug($video->description, $postIdsBySlug->keys()->all());

            if ($slug === null) {
                $unmatched++;

                continue;
            }

            $postId = $postIdsBySlug->get($slug);

            $this->line("  #{$video->id} « {$video->title} »  →  {$slug}");

            if (! $dryRun) {
                $video->update(['related_post_id' => $postId]);
            }
            $linked++;
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info(sprintf(
            '%s%d vidéo(s) associée(s) à un article, %d sans lien d\'article exploitable.',
            $prefix,
            $linked,
            $unmatched,
        ));

        return self::SUCCESS;
    }

    /**
     * Extrait le premier slug d'article vivre-pleinement.fr présent dans la
     * description et correspondant à un article réellement publié.
     *
     * @param  list<string>  $knownSlugs
     */
    private function extractArticleSlug(string $description, array $knownSlugs): ?string
    {
        if (! preg_match_all('#vivre-pleinement\.fr/([a-z0-9-]{4,})/?#i', $description, $matches)) {
            return null;
        }

        foreach ($matches[1] as $candidate) {
            $candidate = strtolower($candidate);

            if (in_array($candidate, self::NON_ARTICLE_SLUGS, true)) {
                continue;
            }

            if (in_array($candidate, $knownSlugs, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
