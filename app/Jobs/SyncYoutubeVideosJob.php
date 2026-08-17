<?php

namespace App\Jobs;

use App\Services\YoutubeSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncYoutubeVideosJob implements ShouldQueue
{
    use Queueable;

    /**
     * L'API YouTube peut être momentanément indisponible ou limitée en quota :
     * on retente avant d'abandonner.
     */
    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    /**
     * La synchronisation parcourt la playlist page par page (50 éléments), puis
     * récupère le détail des vidéos par lots de 50, chaque appel disposant de
     * 15 s et de deux tentatives. Un catalogue complet dépasse donc largement
     * les 60 s du délai par défaut d'un worker, qui tuerait le job en plein
     * traitement et le rejouerait sur un catalogue à moitié écrit.
     *
     * Doit rester inférieur au `retry_after` de la connexion de file
     * (config/queue.php), sans quoi un second worker reprendrait le job avant
     * la fin du premier.
     */
    public int $timeout = 300;

    /**
     * Exécute la synchronisation des vidéos YouTube en arrière-plan.
     */
    public function handle(): void
    {
        $result = YoutubeSync::fromConfig()->sync();

        Log::info('Synchronisation YouTube terminée.', $result);
    }

    /**
     * Le catalogue reste figé sur sa dernière version connue : ça doit laisser
     * une trace exploitable plutôt que de finir en silence.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Synchronisation YouTube définitivement échouée.', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
