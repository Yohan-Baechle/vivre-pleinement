<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('seo:maillage')]
#[Description('Audite le maillage interne du blog : orphelins, clusters sans pilier, piliers invalides.')]
class AuditInternalLinking extends Command
{
    public function handle(): int
    {
        $orphans = $this->orphanPosts();
        $withoutPillar = $this->categoriesWithoutPillar();
        $invalidPillars = $this->categoriesWithInvalidPillar();

        $this->reportOrphans($orphans);
        $this->reportCategoriesWithoutPillar($withoutPillar);
        $this->reportInvalidPillars($invalidPillars);

        $problems = $orphans->count() + $withoutPillar->count() + $invalidPillars->count();

        $this->newLine();

        if ($problems === 0) {
            $this->info('Maillage interne sain : aucun problème détecté.');

            return self::SUCCESS;
        }

        $this->warn("{$problems} problème(s) de maillage à corriger.");

        return self::FAILURE;
    }

    /**
     * Articles publiés sans aucune catégorie : ni similaires pertinents, ni pilier.
     *
     * @return Collection<int, Post>
     */
    private function orphanPosts(): Collection
    {
        return Post::query()
            ->published()
            ->whereDoesntHave('categories')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    /**
     * Catégories ayant des articles mais aucun pilier défini : leurs articles
     * n'affichent pas le bloc « Pour aller plus loin ».
     *
     * @return Collection<int, Category>
     */
    private function categoriesWithoutPillar(): Collection
    {
        return Category::query()
            ->whereNull('pillar_post_id')
            ->whereHas('posts')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Catégories dont le pilier est dépublié, supprimé ou hors de la catégorie.
     *
     * @return Collection<int, Category>
     */
    private function categoriesWithInvalidPillar(): Collection
    {
        return Category::query()
            ->whereNotNull('pillar_post_id')
            ->with('pillarPost')
            ->get(['id', 'name', 'slug', 'pillar_post_id'])
            ->filter(function (Category $category): bool {
                $pillar = $category->pillarPost;

                return $pillar === null
                    || $pillar->status !== PostStatus::Published
                    || ! $category->posts()->whereKey($pillar->id)->exists();
            })
            ->values();
    }

    /**
     * @param  Collection<int, Post>  $orphans
     */
    private function reportOrphans(Collection $orphans): void
    {
        $this->components->info('Articles orphelins (sans catégorie)');

        if ($orphans->isEmpty()) {
            $this->line('  Aucun.');

            return;
        }

        foreach ($orphans as $post) {
            $this->line("  ⚠️  {$post->slug} — {$post->title}");
        }
    }

    /**
     * @param  Collection<int, Category>  $categories
     */
    private function reportCategoriesWithoutPillar(Collection $categories): void
    {
        $this->components->info('Catégories sans article pilier');

        if ($categories->isEmpty()) {
            $this->line('  Aucune.');

            return;
        }

        foreach ($categories as $category) {
            $this->line("  ⚠️  {$category->slug} — {$category->name}");
        }
    }

    /**
     * @param  Collection<int, Category>  $categories
     */
    private function reportInvalidPillars(Collection $categories): void
    {
        $this->components->info('Catégories au pilier invalide (dépublié, supprimé ou hors catégorie)');

        if ($categories->isEmpty()) {
            $this->line('  Aucune.');

            return;
        }

        foreach ($categories as $category) {
            $this->line("  ⚠️  {$category->slug} — pilier #{$category->pillar_post_id} invalide");
        }
    }
}
