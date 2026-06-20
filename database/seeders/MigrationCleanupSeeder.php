<?php

namespace Database\Seeders;

use App\Console\Commands\CleanCommentContent;
use App\Console\Commands\CleanPostContent;
use App\Console\Commands\FixPostSeo;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Post-traitement après import du contenu WordPress (ContentSeeder).
 *
 * Le dump database/seed.sql est déjà nettoyé ; ce seeder reste idempotent et
 * sert surtout à générer les redirections 301. Le nettoyage des commentaires
 * est conservé en filet de sécurité au cas où un dump brut serait réimporté.
 */
class MigrationCleanupSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Comment::query()->where('author_name', 'Laura J.')->update(['author_name' => 'Laura B.']);

        foreach (Comment::query()->cursor() as $comment) {
            $clean = CleanCommentContent::clean($comment->content);

            if ($clean !== $comment->content) {
                $comment->forceFill(['content' => $clean])->saveQuietly();
            }
        }

        foreach (Post::query()->cursor() as $post) {
            $clean = FixPostSeo::fix(CleanPostContent::clean($post->content));

            if ($clean !== $post->content) {
                $post->forceFill(['content' => $clean])->saveQuietly();
            }
        }

        Artisan::call('seo:wp-redirects');
    }
}
