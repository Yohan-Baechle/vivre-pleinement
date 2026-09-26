<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->environments(['production']);

/**
 * Filet de sécurité des paiements : rattrape ce qu'un webhook perdu aurait
 * laissé en plan. Sans elle, un client débité par Stripe peut rester sans
 * rendez-vous, sans accès ni livre, et personne n'en est averti.
 */
Schedule::command('payments:reconcile')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->environments(['production']);

/**
 * Chaîne de publication des vidéos : la synchro crée les nouvelles vidéos,
 * puis leurs sous-titres sont récupérés. YouTube met parfois plusieurs heures
 * à générer les sous-titres automatiques : une vidéo sans sous-titre est
 * simplement retentée au passage suivant. La fenêtre de 14 jours protège le
 * quota de l'API (50 unités par vidéo interrogée). La mise en forme IA est
 * ensuite pilotée par n8n via /api/automation/videos.
 */
Schedule::command('youtube:sync')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->environments(['production']);

Schedule::command('youtube:fetch-transcripts --since=14')
    ->hourlyAt(10)
    ->withoutOverlapping()
    ->onOneServer()
    ->environments(['production']);
