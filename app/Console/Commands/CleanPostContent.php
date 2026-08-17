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
     * Déroule les <span> sans attribut (résidus Gutenberg) en conservant leur
     * texte, en répétant la passe pour gérer l'imbrication. Les <span ...>
     * porteurs d'attributs sont laissés intacts.
     *
     * Retire ensuite les résidus WordPress : appels au don Tipeee sous toutes
     * leurs variantes (emojis devenus « ???? » à la migration inclus),
     * commentaires de blocs Divi et paragraphes vides orphelins.
     */
    public static function clean(string $content): string
    {
        do {
            $content = preg_replace('#<span>(.*?)</span>#is', '$1', $content, -1, $count);
        } while ($count > 0);

        $donationPhrases = [
            'Si vous aimez mon travail',
            'Si vous appréciez mon travail',
            'si mon travail vous aide',
            'soutenir mon travail',
            'Tipeee',
        ];

        foreach ($donationPhrases as $phrase) {
            $content = preg_replace('#\s*<p>[^<]*'.preg_quote($phrase, '#').'.*?</p>#isu', '', $content);
        }

        $content = preg_replace('#<!--\s*/?divi:.*?-->#is', '', $content);
        $content = preg_replace('#\s*<p>(?:\s|&nbsp;)*</p>#is', '', $content);

        return $content;
    }
}
