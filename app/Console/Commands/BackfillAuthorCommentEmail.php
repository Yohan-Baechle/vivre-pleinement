<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Support\SiteContact;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('comments:backfill-author-email {--dry-run : Affiche les commentaires à corriger sans les enregistrer}')]
#[Description("Renseigne l'e-mail de l'auteure sur ses réponses importées de WordPress (author_email vide) afin que sa photo et son badge s'affichent.")]
class BackfillAuthorCommentEmail extends Command
{
    /**
     * Nom utilisé par l'auteure dans ses réponses importées de WordPress.
     */
    private const AUTHOR_NAME = 'Laura B.';

    public function handle(): int
    {
        $email = SiteContact::email();

        $comments = Comment::query()
            ->where('author_name', self::AUTHOR_NAME)
            ->whereNull('author_email')
            ->get();

        if ($comments->isEmpty()) {
            $this->info('Aucun commentaire à corriger.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            foreach ($comments as $comment) {
                $this->line("À corriger : commentaire #{$comment->id}");
            }

            $this->info("{$comments->count()} commentaire(s) à corriger (e-mail : {$email}).");

            return self::SUCCESS;
        }

        foreach ($comments as $comment) {
            $comment->forceFill(['author_email' => $email])->saveQuietly();
        }

        $this->info("{$comments->count()} commentaire(s) corrigé(s) avec l'e-mail {$email}.");

        return self::SUCCESS;
    }
}
