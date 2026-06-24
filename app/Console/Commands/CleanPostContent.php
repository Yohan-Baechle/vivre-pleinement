<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:clean-content {--dry-run : Affiche les articles à nettoyer sans les enregistrer}')]
#[Description('Retire les <span> parasites et les paragraphes « Si vous aimez mon travail » du contenu des articles importés de WordPress.')]
class CleanPostContent extends Command
{
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $changed = 0;

        foreach (Post::query()->cursor() as $post) {
            $clean = self::clean($post->content);

            if ($clean === $post->content) {
                continue;
            }

            $changed++;

            if ($dryRun) {
                $this->line("À nettoyer : {$post->slug}");

                continue;
            }

            $post->forceFill(['content' => $clean])->saveQuietly();
        }

        $verb = $dryRun ? 'à nettoyer' : 'nettoyé(s)';
        $this->info("{$changed} article(s) {$verb}.");

        return self::SUCCESS;
    }

    /**
     * Déroule les <span> sans attribut (résidus Gutenberg) en conservant leur texte,
     * en répétant la passe pour gérer l'imbrication. Les <span ...> porteurs
     * d'attributs sont laissés intacts.
     */
    public static function clean(string $content): string
    {
        do {
            $content = preg_replace('#<span>(.*?)</span>#is', '$1', $content, -1, $count);
        } while ($count > 0);

        // Retire les paragraphes d'appel au don « Si vous aimez mon travail »
        // (résidus WordPress/Tipeee), avec leurs éventuels sauts de ligne alentour.
        $content = preg_replace('#\s*<p>[^<]*Si vous aimez mon travail.*?</p>#is', '', $content);

        return $content;
    }
}
