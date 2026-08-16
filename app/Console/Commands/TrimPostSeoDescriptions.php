<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:trim-seo-descriptions {--dry-run : Affiche les changements sans les enregistrer}')]
#[Description('Retaille les meta descriptions dépassant 155 caractères pour éviter la troncature dans les SERP.')]
class TrimPostSeoDescriptions extends Command
{
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $changed = 0;

        foreach ($this->descriptions() as $slug => $description) {
            $post = Post::query()->where('slug', $slug)->first();

            if (! $post) {
                $this->warn("Article introuvable : {$slug}");

                continue;
            }

            if ($post->seo_description === $description) {
                continue;
            }

            $changed++;
            $this->line(($dry ? '[dry] ' : '')."✓ {$slug} — ".mb_strlen($description).' car.');

            if ($dry) {
                continue;
            }

            $post->seo_description = $description;
            $post->save();
        }

        $this->newLine();
        $this->comment(($dry ? '[dry] ' : '')."{$changed} description(s) retaillée(s).");

        return self::SUCCESS;
    }

    /**
     * Descriptions ≤ 155 caractères par slug, réécrites sans perdre
     * l'intention.
     *
     * @return array<string, string>
     */
    private function descriptions(): array
    {
        return [
            'accords-tolteques' => 'Quels sont les 5 accords toltèques de Don Miguel Ruiz ? Résumé clair de chaque accord et conseils concrets pour les appliquer au quotidien.',
            'trouble-anxieux-generalise' => 'Le trouble anxieux généralisé (TAG) vous épuise à force de tout anticiper ? Comprenez ses causes et découvrez comment vous en sortir durablement.',
            'ergophobie-peur-du-travail' => "L'idée d'aller travailler vous noue l'estomac ? L'ergophobie est une vraie phobie, différente du burn-out. Symptômes, causes et solutions concrètes.",
            'symbolique-des-reves' => "Que signifient vos rêves et cauchemars ? Origines des rêves, interprétation et ce qu'ils révèlent de vos émotions et de votre anxiété.",
            'vivre-linstant-present' => "Vivre l'instant présent, ça veut dire quoi ? 11 exercices simples de pleine conscience pour sortir du mental et apaiser l'anxiété.",
            'angoisse-matinale' => "Anxiété dès le réveil ? L'angoisse matinale se soigne. Comprenez ses mécanismes et découvrez comment l'apaiser durablement.",
            'troubles-de-la-personnalite' => 'Quels sont les différents troubles de la personnalité ? Comment les reconnaître et les soulager quand le tempérament fait souffrir ?',
            'respiration' => "Mal respirer peut entretenir l'anxiété. Découvrez les bienfaits d'une bonne respiration et comment en profiter au quotidien.",
            'etre-casanier' => 'Être casanier est souvent mal perçu par la société, à tort. Découvrez comment assumer votre tempérament casanier et en être fier.',
        ];
    }
}
