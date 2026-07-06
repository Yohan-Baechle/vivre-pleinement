<?php

namespace App\Filament\Admin\Resources\Videos\Pages;

use App\Filament\Admin\Resources\Videos\VideoResource;
use App\Filament\Admin\Widgets\VideoStatsOverview;
use App\Jobs\SyncYoutubeVideosJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVideos extends ListRecords
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            VideoStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Synchroniser depuis YouTube')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Synchroniser les vidéos depuis YouTube')
                ->modalDescription('La synchronisation récupère les vidéos publiées sur la chaîne, met à jour les compteurs et marque comme "manquantes" les vidéos disparues. Les champs verrouillés ne sont pas modifiés.')
                ->modalSubmitActionLabel('Lancer la synchronisation')
                ->action(function (): void {
                    dispatch(new SyncYoutubeVideosJob);

                    Notification::make()
                        ->title('Synchronisation lancée')
                        ->body('La synchronisation avec YouTube s\'exécute en arrière-plan.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
