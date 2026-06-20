<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('comments:clean-content {--dry-run : Affiche les changements sans les enregistrer}')]
#[Description('Nettoie le contenu des commentaires importés de WordPress (entités HTML, balises <p>).')]
class CleanCommentContent extends Command
{
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $changed = 0;

        foreach (Comment::query()->cursor() as $comment) {
            $clean = self::clean($comment->content);

            if ($clean === $comment->content) {
                continue;
            }

            $changed++;

            if ($dryRun) {
                $this->line("#{$comment->id} : ".mb_substr($clean, 0, 70));

                continue;
            }

            $comment->forceFill(['content' => $clean])->saveQuietly();
        }

        $verb = $dryRun ? 'à nettoyer' : 'nettoyé(s)';
        $this->info("{$changed} commentaire(s) {$verb}.");

        return self::SUCCESS;
    }

    /**
     * Transforme un contenu HTML WordPress en texte brut propre.
     */
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
