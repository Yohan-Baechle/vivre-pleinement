<?php

use App\Http\Controllers\Automation\VideoPipelineController;
use App\Http\Middleware\EnsureAutomationTokenIsValid;
use Illuminate\Support\Facades\Route;

/**
 * Mise en forme IA des vidéos, pilotée par n8n (jeton AUTOMATION_TOKEN).
 */
Route::prefix('automation/videos')
    ->name('automation.videos.')
    ->middleware([EnsureAutomationTokenIsValid::class, 'throttle:60,1'])
    ->controller(VideoPipelineController::class)
    ->group(function (): void {
        Route::get('transcripts/pending', 'pendingTranscripts')
            ->name('transcripts.pending');
        Route::put('{video:id}/transcript', 'storeTranscript')
            ->name('transcript.store');
        Route::get('enrichments/pending', 'pendingEnrichments')
            ->name('enrichments.pending');
        Route::put('{video:id}/enrichment', 'storeEnrichment')
            ->name('enrichment.store');
    });
