<?php

namespace App\Jobs;

use App\Support\BrevoNewsletter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SubscribeToNewsletterJob implements ShouldQueue
{
    use Queueable;

    /**
     * Crée une nouvelle instance du job.
     */
    public function __construct(
        public string $email,
        public string $firstName,
        public string $redirectionUrl,
    ) {}

    /**
     * Exécute l'inscription à la liste Brevo en arrière-plan.
     */
    public function handle(BrevoNewsletter $newsletter): void
    {
        $newsletter->subscribeToVideoList($this->email, $this->firstName, $this->redirectionUrl);
    }
}
