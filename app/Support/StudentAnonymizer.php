<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentAnonymizer
{
    /**
     * Pseudonymise les données personnelles de l'élève (droit à l'effacement RGPD)
     * tout en conservant les inscriptions pour les obligations comptables (facturation,
     * conservation légale de 10 ans en France). La progression de leçons est supprimée.
     */
    public static function anonymize(Student $student): void
    {
        $student->lessonProgress()->delete();

        $student->forceFill([
            'name' => 'Compte supprimé',
            'email' => 'anonyme+'.$student->id.'@vivre-pleinement.invalid',
            'password' => Hash::make(Str::random(40)),
            'remember_token' => null,
            'anonymized_at' => now(),
        ])->save();
    }
}
