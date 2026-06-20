<?php

namespace App\Support;

use App\Models\Appointment;
use Carbon\CarbonInterface;

/**
 * Génère le contenu d'un fichier iCalendar (.ics) conforme RFC 5545
 * pour un rendez-vous, afin que le visiteur l'ajoute à son agenda.
 */
class IcsCalendar
{
    private const LINE_LIMIT = 75;

    public static function forAppointment(Appointment $appointment): string
    {
        $title = 'RDV – '.$appointment->service->name;
        $description = 'Rendez-vous en visioconférence avec Laura Baechlé. Référence : '.$appointment->reference;
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Vivre Pleinement//Reservation//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$appointment->reference.'@'.$host,
            'DTSTAMP:'.self::formatDate(now()),
            'DTSTART:'.self::formatDate($appointment->starts_at),
            'DTEND:'.self::formatDate($appointment->ends_at),
            'SUMMARY:'.self::escape($title),
            'DESCRIPTION:'.self::escape($description),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map(self::foldLine(...), $lines));
    }

    private static function formatDate(CarbonInterface $date): string
    {
        return $date->utc()->format('Ymd\THis\Z');
    }

    private static function escape(string $text): string
    {
        return addcslashes($text, ",;\\\n");
    }

    /**
     * Replie une ligne de contenu à 75 octets par ligne (les continuations débutent par une espace).
     */
    private static function foldLine(string $line): string
    {
        if (strlen($line) <= self::LINE_LIMIT) {
            return $line;
        }

        $folded = '';
        $current = '';

        foreach (mb_str_split($line) as $char) {
            if (strlen($current.$char) > self::LINE_LIMIT) {
                $folded .= ($folded === '' ? '' : "\r\n ").$current;
                $current = '';
            }
            $current .= $char;
        }

        return $folded.($folded === '' ? '' : "\r\n ").$current;
    }
}
