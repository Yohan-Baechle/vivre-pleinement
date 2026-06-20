<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:fix-seo {--dry-run : Affiche les articles à corriger sans les enregistrer}')]
#[Description('Corrige la sémantique SEO du contenu des articles (h1 en double, alt manquants, liens d\'affiliation).')]
class FixPostSeo extends Command
{
    /**
     * Domaines d'affiliation dont les liens doivent être marqués sponsorisés (exigence Google).
     *
     * @var array<string>
     */
    private const AFFILIATE_HOSTS = ['1tpe.net', 'amzn.to', 'amazon.fr', 'amazon.com', 'hypnocaments.com', 'ruedesplantes.com'];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $changed = 0;

        foreach (Post::query()->cursor() as $post) {
            $clean = self::fix($post->content);

            if ($clean === $post->content) {
                continue;
            }

            $changed++;

            if ($dryRun) {
                $this->line("À corriger : {$post->slug}");

                continue;
            }

            $post->forceFill(['content' => $clean])->saveQuietly();
        }

        $verb = $dryRun ? 'à corriger' : 'corrigé(s)';
        $this->info("{$changed} article(s) {$verb}.");

        return self::SUCCESS;
    }

    public static function fix(string $content): string
    {
        $content = self::demoteContentH1($content);
        $content = self::addAltToYogaBanners($content);
        $content = self::removeOrphanYogaBanners($content);
        $content = self::replaceBrokenSmileys($content);

        return self::markAffiliateLinks($content);
    }

    /**
     * Supprime les bannières yoga orphelines (image seule, sans lien d'affiliation
     * autour) : ce sont d'anciennes pubs sans cible, donc sans valeur. Les bannières
     * correctement liées sont protégées par un marqueur le temps de la suppression.
     */
    private static function removeOrphanYogaBanners(string $content): string
    {
        $content = preg_replace_callback(
            '#<a\b[^>]*>\s*<img\b[^>]*yoga_300x250[^>]*>\s*</a>#i',
            fn (array $m): string => str_replace('<img', '<img data-keep', $m[0]),
            $content,
        );

        $content = preg_replace('#<img\b(?![^>]*data-keep)[^>]*yoga_300x250[^>]*>#i', '', $content);

        return str_replace('<img data-keep', '<img', $content);
    }

    /**
     * Remplace les anciens smileys Divi (images vers l'ancien WordPress) par
     * l'emoji Unicode correspondant, sinon ils seront cassés une fois le WP éteint.
     */
    private static function replaceBrokenSmileys(string $content): string
    {
        return preg_replace('#<img[^>]*smiley-smile[^>]*>#i', '🙂', $content);
    }

    /**
     * Le <h1> du contenu fait doublon avec le titre de page : on le rétrograde en <h2>.
     */
    private static function demoteContentH1(string $content): string
    {
        $content = preg_replace('#<h1(\s[^>]*)?>#i', '<h2>', $content);

        return preg_replace('#</h1>#i', '</h2>', $content);
    }

    /**
     * Donne un alt descriptif aux bandeaux d'affiliation yoga (alt vide).
     */
    private static function addAltToYogaBanners(string $content): string
    {
        return preg_replace(
            '#(<img\b[^>]*\byoga_300x250[^>]*\b)alt=""#i',
            '$1alt="Séance de yoga pour apaiser l\'anxiété"',
            $content,
        );
    }

    /**
     * Marque les liens d'affiliation comme sponsorisés et ouverts dans un nouvel onglet.
     */
    private static function markAffiliateLinks(string $content): string
    {
        $hosts = implode('|', array_map(fn (string $h): string => preg_quote($h, '#'), self::AFFILIATE_HOSTS));

        return preg_replace_callback(
            '#<a\b([^>]*\b(?:'.$hosts.')[^>]*)>#i',
            function (array $m): string {
                $attrs = $m[1];

                if (preg_match('/\brel="([^"]*)"/i', $attrs, $rel)) {
                    $values = preg_split('/\s+/', trim($rel[1]));
                    foreach (['nofollow', 'sponsored', 'noopener'] as $needed) {
                        if (! in_array($needed, $values, true)) {
                            $values[] = $needed;
                        }
                    }
                    $attrs = preg_replace('/\brel="[^"]*"/i', 'rel="'.implode(' ', $values).'"', $attrs);
                } else {
                    $attrs .= ' rel="nofollow sponsored noopener"';
                }

                if (stripos($attrs, 'target=') === false) {
                    $attrs .= ' target="_blank"';
                }

                return '<a'.$attrs.'>';
            },
            $content,
        );
    }
}
