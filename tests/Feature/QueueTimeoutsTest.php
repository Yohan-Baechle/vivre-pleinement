<?php

use App\Jobs\SubscribeToNewsletterJob;
use App\Jobs\SyncYoutubeVideosJob;

/**
 * Un `retry_after` inférieur au `$timeout` d'un job fait remettre
 * celui-ci en circulation alors qu'il tourne encore : deux workers le
 * traitent alors en parallèle. La marge doit rester vérifiée à chaque
 * ajout de job long.
 */
it('keeps every job timeout below the queue retry_after', function () {
    $jobs = [
        new SyncYoutubeVideosJob,
        new SubscribeToNewsletterJob('a@b.test', 'Test', 'https://example.test'),
    ];

    foreach (['database', 'redis'] as $connection) {
        $retryAfter = config("queue.connections.{$connection}.retry_after");

        foreach ($jobs as $job) {
            expect($job->timeout ?? 60)->toBeLessThan($retryAfter);
        }
    }
});

/**
 * La synchronisation parcourt la playlist puis le détail des vidéos par
 * lots : elle dépasse les 60 s du délai par défaut d'un worker.
 */
it('gives the youtube sync enough time to walk the whole catalogue', function () {
    expect((new SyncYoutubeVideosJob)->timeout)->toBeGreaterThanOrEqual(300);
});
