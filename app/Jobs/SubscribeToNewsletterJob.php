<?php

namespace App\Jobs;

use App\Support\BrevoNewsletter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubscribeToNewsletterJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Brevo peut être momentanément indisponible : on retente avant
     * d'abandonner.
     */
    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120];

    public function __construct(
        public string $email,
        public string $firstName,
        public string $redirectionUrl,
    ) {}

    /**
     * Évite d'empiler plusieurs inscriptions pour la même adresse quand un
     * visiteur soumet le formulaire à répétition.
     */
    public function uniqueId(): string
    {
        return $this->email;
    }

    /**
     * Exécute l'inscription à la liste Brevo en arrière-plan.
     */
    public function handle(BrevoNewsletter $newsletter): void
    {
        $newsletter->subscribeToVideoList($this->email, $this->firstName, $this->redirectionUrl);
    }

    /**
     * Le visiteur a laissé son adresse mais n'entrera jamais dans la liste :
     * ça doit laisser une trace exploitable plutôt que de finir en silence.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Inscription newsletter Brevo définitivement échouée.', [
            'email' => $this->email,
            'exception' => $exception->getMessage(),
        ]);
    }
}
