<?php

use App\Support\Weekdays;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * Horodatage de formulaire chiffré, antidaté pour franchir le délai minimum
 * exigé par ChecksSubmissionDelay. `$secondsAgo = 0` simule une soumission
 * instantanée, donc robotique.
 */
function submissionStamp(int $secondsAgo = 5): string
{
    return Crypt::encryptString((string) (time() - $secondsAgo));
}

/**
 * Construit un état de formulaire complet : tous les jours fermés, sauf ceux
 * décrits par $openDays (clé = dayOfWeek Carbon, valeur = liste de plages).
 *
 * @param  array<int, array<int, array{start_time: string, end_time: string}>>  $openDays
 * @return array<string, mixed>
 */
function scheduleFormState(array $openDays, ?int $serviceId = null): array
{
    $days = [];

    foreach (Weekdays::orderedKeys() as $day) {
        $days["day_{$day}"] = [
            'is_open' => isset($openDays[$day]),
            'ranges' => $openDays[$day] ?? [],
        ];
    }

    return [
        'appointment_service_id' => $serviceId,
        'days' => $days,
    ];
}
