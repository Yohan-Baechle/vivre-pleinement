<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Support\CommentSanitizer;
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
            $clean = CommentSanitizer::clean($comment->content);

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
}
