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
     * Exécute la synchronisation des vidéos YouTube en arrière-plan.
     */
    public function handle(): void
    {
        try {
            $result = YoutubeSync::fromConfig()->sync();
        } catch (Throwable $e) {
            Log::error('Échec de la synchronisation YouTube.', ['message' => $e->getMessage()]);

            throw $e;
        }

        Log::info('Synchronisation YouTube terminée.', $result);
    }
}
