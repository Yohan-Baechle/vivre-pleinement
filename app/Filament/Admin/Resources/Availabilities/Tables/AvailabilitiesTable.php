<?php

namespace App\Filament\Admin\Resources\Availabilities\Tables;

use App\Models\AppointmentService;
use App\Support\Weekdays;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AvailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(null)
            ->emptyStateIcon(Heroicon::OutlinedClock)
            ->emptyStateHeading('Aucune plage horaire')
            ->emptyStateDescription('Définissez vos horaires depuis la page '
                .'« Horaires de la semaine ».')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->orderByRaw(Weekdays::sortExpression())
                ->orderBy('start_time'))
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->formatStateUsing(fn (int $state) => Weekdays::label($state)),

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
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Jour')
                    ->options(Weekdays::labels()),

                SelectFilter::make('appointment_service_id')
                    ->label('Prestation')
                    ->options(fn () => AppointmentService::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')),

                TernaryFilter::make('is_active')
                    ->label('Active'),
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
