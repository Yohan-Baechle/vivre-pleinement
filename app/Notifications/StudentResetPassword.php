<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StudentResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Construit le lien de réinitialisation pointant vers l'espace élève.
     */
    protected function resetUrl($notifiable): string
    {
        return route('student.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour,')
            ->line('Vous recevez cet e-mail car une réinitialisation de mot de passe a été demandée pour votre compte formation.')
            ->action('Réinitialiser le mot de passe', $this->resetUrl($notifiable))
            ->line('Ce lien expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, aucune action n'est requise.");
    }
}
