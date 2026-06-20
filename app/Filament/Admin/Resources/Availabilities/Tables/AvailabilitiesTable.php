<?php

namespace App\Filament\Admin\Resources\Availabilities\Tables;

use App\Filament\Admin\Resources\Availabilities\AvailabilityResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('day_of_week')
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->formatStateUsing(fn (int $state) => AvailabilityResource::weekdays()[$state] ?? '–')
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Prestation')
                    ->placeholder('Toutes')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Début')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([])
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
