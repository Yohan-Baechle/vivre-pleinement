<?php

namespace App\Filament\Admin\Resources\Videos\Pages;

use App\Filament\Admin\Resources\Videos\VideoResource;
use App\Filament\Admin\Widgets\VideoStatsOverview;
use App\Services\YoutubeSync;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

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
                    try {
                        $result = YoutubeSync::fromConfig()->sync();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Synchronisation échouée')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Synchronisation terminée')
                        ->body(sprintf(
                            '%d créée(s), %d mise(s) à jour, %d manquante(s).',
                            $result['created'],
                            $result['updated'],
                            $result['missing'],
                        ))
                        ->success()
                        ->send();
                }),
        ];
    }
}
