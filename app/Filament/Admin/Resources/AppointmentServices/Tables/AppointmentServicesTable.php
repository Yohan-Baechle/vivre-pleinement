<?php

namespace App\Filament\Admin\Resources\AppointmentServices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AppointmentServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->emptyStateIcon(Heroicon::OutlinedSparkles)
            ->emptyStateHeading('Aucune prestation')
            ->emptyStateDescription('Une prestation décrit ce que le client '
                .'réserve : sa durée, son prix et son délai de réservation.')
            ->emptyStateActions([
                CreateAction::make()->label('Créer une prestation'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Durée')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('price_cents')
                    ->label('Prix')
                    ->money('EUR', divideBy: 100)
                    ->sortable(),

                TextColumn::make('appointments_count')
                    ->label('Rendez-vous')
                    ->counts('appointments')
                    ->sortable(),

                IconColumn::make('requires_confirmation')
                    ->label('Demande ma validation')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
