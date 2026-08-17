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
