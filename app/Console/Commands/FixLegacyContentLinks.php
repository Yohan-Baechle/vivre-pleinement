<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:fix-legacy-links {--dry-run : Liste les réécritures sans enregistrer}')]
#[Description('Réécrit les liens internes hérités de WordPress (href="/slug") vers les URLs canoniques /blog/... pour éviter les sauts de 301 dans le contenu des articles.')]
class FixLegacyContentLinks extends Command
{
    /**
     * Chemins racine légitimes du nouveau site, à laisser intacts sans
     * avertissement.
     */
    private const CURRENT_ROOT_PATHS = [
        'contact', 'blog', 'videos', 'formations', 'livre', 'reservation',
        'a-propos', 'mentions-legales', 'politique-cookies',
        'politique-de-confidentialite', 'conditions-generales-de-vente',
        'inscription-confirmee', 'espace-formation', 'newsletter',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $postSlugs = Post::query()->pluck('slug')->flip();
        $categorySlugs = Category::query()->pluck('slug')->flip();

        $changedPosts = 0;
        $rewritten = 0;
        $unknown = [];

        foreach (Post::query()->cursor() as $post) {
            $content = $this->rewrite($post->content, $postSlugs->all(), $categorySlugs->all(), $count, $unknown);

            if ($content === $post->content) {
                continue;
            }

            $changedPosts++;
            $rewritten += $count;
            $this->line(sprintf('%s%s : %d lien(s) réécrit(s)', $dryRun ? '[DRY-RUN] ' : '', $post->slug, $count));

            if (! $dryRun) {
                Post::withoutTimestamps(
                    fn () => $post->forceFill(['content' => $content])->saveQuietly()
                );
            }
        }

        $this->info(sprintf(
            '%s%d lien(s) réécrit(s) dans %d article(s).',
            $dryRun ? '[DRY-RUN] ' : '',
            $rewritten,
            $changedPosts,
        ));

        if ($unknown !== []) {
            $this->newLine();
            $this->warn('Liens racine laissés intacts (aucun article ou catégorie correspondant) :');
            foreach (array_unique($unknown) as $path) {
                $this->line("  {$path}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Réécrit href="/slug" → /blog/slug et href="/category/slug" →
     * /blog/categorie/slug, uniquement quand le slug correspond à un article ou
     * une catégorie existants. Les liens absolus vivre-pleinement.fr sont
     * traités de la même façon ; les chemins racine du nouveau site restent
     * intacts.
     *
     * @param  array<string, int>  $postSlugs
     * @param  array<string, int>  $categorySlugs
     * @param  list<string>  $unknown
     */
    public function rewrite(string $content, array $postSlugs, array $categorySlugs, ?int &$count = 0, array &$unknown = []): string
    {
        $count = 0;

        return preg_replace_callback(
            '#href="(?:https?://(?:www\.)?vivre-pleinement\.fr)?/(category/)?([a-z0-9-]+)/?"#i',
            function (array $m) use ($postSlugs, $categorySlugs, &$count, &$unknown) {
                $slug = strtolower($m[2]);

                if ($m[1] !== '') {
                    if (isset($categorySlugs[$slug])) {
                        $count++;

                        return 'href="/blog/categorie/'.$slug.'"';
                    }

                    $unknown[] = '/category/'.$slug;

                    return $m[0];
                }

                if (in_array($slug, self::CURRENT_ROOT_PATHS, true)) {
                    return $m[0];
                }

                if (isset($postSlugs[$slug])) {
                    $count++;

                    return 'href="/blog/'.$slug.'"';
                }

                $unknown[] = '/'.$slug;

                return $m[0];
            },
            $content,
        );
    }
}
