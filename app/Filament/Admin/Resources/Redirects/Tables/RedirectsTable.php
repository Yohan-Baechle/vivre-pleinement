<?php

namespace App\Filament\Admin\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedArrowPathRoundedSquare)
            ->emptyStateHeading('Aucune redirection')
            ->emptyStateDescription('Une redirection envoie les visiteurs '
                .'d\'une ancienne adresse vers la nouvelle, sans erreur 404.')
            ->emptyStateActions([
                CreateAction::make()->label('Créer une redirection'),
            ])
            ->columns([
                TextColumn::make('from_path')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('to_path')
                    ->label('Cible')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('status_code')
                    ->label('Code')
                    ->badge(),

                TextColumn::make('hit_count')
                    ->label('Visites')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('last_hit_at')
                    ->label('Dernière visite')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('–')
                    ->sortable(),
            ])
            ->defaultSort('hit_count', 'desc')
            ->filters([
                SelectFilter::make('status_code')
                    ->label('Code HTTP')
                    ->options([
                        301 => '301',
                        302 => '302',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
