<?php

namespace App\Filament\Admin\Pages;

use App\Enums\AppointmentStatus;
use App\Enums\CommentStatus;
use App\Models\Appointment;
use App\Models\Comment;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de bord';

    protected static ?string $navigationLabel = 'Tableau de bord';

    /**
     * Répond en une phrase à la seule question qu'on se pose en arrivant :
     * est-ce que quelque chose m'attend ? Les compteurs détaillés vivent dans
     * les widgets, le sous-titre dit juste s'il faut s'y arrêter.
     */
    public function getSubheading(): ?string
    {
        $waiting = [];

        $appointments = Appointment::query()
            ->where('status', AppointmentStatus::Pending)
            ->count();

        if ($appointments > 0) {
            $waiting[] = trans_choice(
                '{1} :count rendez-vous à confirmer'
                    .'|[2,*] :count rendez-vous à confirmer',
                $appointments,
                ['count' => $appointments],
            );
        }

        $comments = Comment::query()
            ->where('status', CommentStatus::Pending)
            ->count();

        if ($comments > 0) {
            $waiting[] = trans_choice(
                '{1} :count commentaire à modérer'
                    .'|[2,*] :count commentaires à modérer',
                $comments,
                ['count' => $comments],
            );
        }

        if ($waiting === []) {
            return 'Rien ne vous attend : tout est traité.';
        }

        return 'À traiter aujourd\'hui : '.implode(' et ', $waiting).'.';
    }
}
