<?php

namespace App\Filament\Admin\Resources\Students\Pages;

use App\Filament\Admin\Resources\Students\StudentResource;
use App\Support\StudentAnonymizer;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Nom'),
            TextEntry::make('email')->label('Email'),
            TextEntry::make('created_at')->label('Inscrit le')->dateTime('d/m/Y'),
            TextEntry::make('anonymized_at')
                ->label('Anonymisé le')
                ->dateTime('d/m/Y')
                ->placeholder('Compte actif'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('anonymize')
                ->label('Supprimer / anonymiser (RGPD)')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => ! $this->record->isAnonymized())
                ->requiresConfirmation()
                ->modalHeading('Anonymiser ce compte élève')
                ->modalDescription('Les données personnelles seront effacées. Les achats sont conservés de façon anonymisée pour les obligations comptables. Action irréversible.')
                ->action(function (): void {
                    StudentAnonymizer::anonymize($this->record);

                    Notification::make()
                        ->title('Compte anonymisé')
                        ->success()
                        ->send();

                    $this->refreshFormData(['name', 'email', 'anonymized_at']);
                }),
        ];
    }
}
