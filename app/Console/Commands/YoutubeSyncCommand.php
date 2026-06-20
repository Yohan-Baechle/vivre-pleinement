<?php

namespace App\Console\Commands;

use App\Services\YoutubeSync;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('youtube:sync {--max=50 : Nombre maximum de vidéos à synchroniser}')]
#[Description('Synchronise les vidéos de la chaîne YouTube configurée vers la table videos.')]
class YoutubeSyncCommand extends Command
{
    public function handle(): int
    {
        try {
            $result = YoutubeSync::fromConfig()->sync(maxResults: (int) $this->option('max'));
        } catch (Throwable $e) {
            $this->error('Échec de la synchronisation : '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Sync OK – %d créée(s), %d mise(s) à jour, %d manquante(s), %d au total.',
            $result['created'],
            $result['updated'],
            $result['missing'],
            $result['total'],
        ));

        return self::SUCCESS;
    }
}
