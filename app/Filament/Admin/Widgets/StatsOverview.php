<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\AppointmentStatus;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Appointment;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Vue d\'ensemble';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            $this->pendingAppointments(),
            $this->pendingComments(),
            $this->posts(),
            $this->products(),
        ];
    }

    /**
     * Première tuile : ce qui bloque quelqu'un d'autre. Un client qui a
     * réservé attend une confirmation, c'est le seul chiffre du tableau de
     * bord qui a une contrepartie humaine.
     */
    private function pendingAppointments(): Stat
    {
        $pending = Appointment::query()
            ->where('status', AppointmentStatus::Pending)
            ->count();

        $thisWeek = Appointment::query()
            ->blocking()
            ->whereBetween('starts_at', [
                CarbonImmutable::now(),
                CarbonImmutable::now()->addWeek(),
            ])
            ->count();

        return Stat::make('Rendez-vous à confirmer', $pending)
            ->description($pending > 0
                ? 'Un client attend votre réponse'
                : $thisWeek.' séance(s) dans les 7 jours')
            ->descriptionIcon($pending > 0
                ? 'heroicon-m-exclamation-triangle'
                : 'heroicon-m-calendar-days')
            ->color($pending > 0 ? 'warning' : 'success')
            ->url(route('filament.admin.resources.appointments.index'));
    }

    private function pendingComments(): Stat
    {
        $pending = Comment::query()
            ->where('status', CommentStatus::Pending)
            ->count();

        return Stat::make('Commentaires à modérer', $pending)
            ->description($pending > 0 ? 'En attente de publication' : 'Rien à modérer')
            ->descriptionIcon($pending > 0
                ? 'heroicon-m-chat-bubble-left-right'
                : 'heroicon-m-check-circle')
            ->color($pending > 0 ? 'warning' : 'gray')
            ->url(route('filament.admin.resources.comments.index'));
    }

    private function posts(): Stat
    {
        $published = Post::query()->where('status', PostStatus::Published)->count();
        $drafts = Post::query()->where('status', PostStatus::Draft)->count();

        return Stat::make('Articles publiés', $published)
            ->description($drafts > 0
                ? $drafts.' brouillon(s) en cours'
                : 'Aucun brouillon en attente')
            ->descriptionIcon('heroicon-m-document-text')
            ->color('success')
            ->url(route('filament.admin.resources.posts.index'));
    }

    private function products(): Stat
    {
        $active = Product::query()->where('is_active', true)->count();

        return Stat::make('Produits en vente', $active)
            ->description('Visibles sur la boutique')
            ->descriptionIcon('heroicon-m-shopping-bag')
            ->color('primary')
            ->url(route('filament.admin.resources.products.index'));
    }
}
