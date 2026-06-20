<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:wp-redirects {--fresh : Supprime les redirections existantes avant de regénérer}')]
#[Description('Génère les redirections 301 depuis les anciennes URLs WordPress vers le nouveau site.')]
class SeedWordPressRedirects extends Command
{
    public function handle(): int
    {
        if ($this->option('fresh')) {
            Redirect::query()->delete();
            $this->warn('Redirections existantes supprimées.');
        }

        $created = 0;

        foreach (Post::query()->pluck('slug') as $slug) {
            $created += $this->upsert("/{$slug}", "/blog/{$slug}");
        }

        foreach (Category::query()->pluck('slug') as $slug) {
            $created += $this->upsert("/category/{$slug}", "/blog/categorie/{$slug}");
        }

        $pageMap = [
            '/prendre-rendez-vous' => '/reservation',
            '/a-propos' => '/#a-propos',
            '/plan-du-site' => '/blog',
            '/credits' => '/mentions-legales',
            '/credits-2' => '/mentions-legales',
            '/produit/ebook-coaching' => '/livre',
            '/categorie-produit/ebook' => '/livre',
            '/categorie-produit/ebook-coaching' => '/livre',
            '/coaching' => '/reservation',
        ];

        foreach ($pageMap as $from => $to) {
            $created += $this->upsert($from, $to);
        }

        $this->info("{$created} redirection(s) créée(s) ou mise(s) à jour.");
        $this->line('Total en base : '.Redirect::query()->count());

        $this->newLine();
        $this->comment('Laissées volontairement en 404 (désindexation automatique par Google) :');
        $this->line('  /boutique, /soutenir-mon-blog (supprimée)');
        $this->line('  /panier, /mon-compte, /commander (déjà en noindex sur WordPress)');

        return self::SUCCESS;
    }

    private function upsert(string $from, string $to): int
    {
        $redirect = Redirect::query()->updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301],
        );

        return $redirect->wasRecentlyCreated || $redirect->wasChanged() ? 1 : 0;
    }
}
