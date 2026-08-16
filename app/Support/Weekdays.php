<?php

namespace App\Support;

class Weekdays
{
    /**
     * Libellés des jours indexés par le dayOfWeek de Carbon (0 = dimanche),
     * dans l'ordre d'affichage français, du lundi au dimanche.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            0 => 'Dimanche',
        ];
    }

    /**
     * Clés des jours dans l'ordre d'affichage, du lundi au dimanche.
     *
     * @return array<int, int>
     */
    public static function orderedKeys(): array
    {
        return array_keys(self::labels());
    }

    public static function label(int $dayOfWeek): string
    {
        return self::labels()[$dayOfWeek] ?? '–';
    }

    /**
     * Expression SQL qui range le dimanche en fin de semaine plutôt qu'en
     * tête, le dayOfWeek de Carbon valant 0 pour ce jour-là.
     */
    public static function sortExpression(string $column = 'day_of_week'): string
    {
        return "CASE WHEN {$column} = 0 THEN 7 ELSE {$column} END";
    }
}
