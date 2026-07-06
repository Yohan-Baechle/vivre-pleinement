<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class StudentVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Construit l'URL signée de vérification pointant vers l'espace élève.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'student.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmez votre adresse e-mail')
            ->greeting('Bonjour,')
            ->line('Merci pour votre inscription à l\'espace formation. Veuillez confirmer votre adresse e-mail pour accéder à vos formations.')
            ->action('Confirmer mon adresse e-mail', $this->verificationUrl($notifiable))
            ->line('Ce lien expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette inscription, aucune action n'est requise.");
    }
}
