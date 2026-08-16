<?php

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
