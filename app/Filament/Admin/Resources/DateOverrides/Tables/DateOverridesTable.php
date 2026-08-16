<?php

namespace App\Filament\Admin\Resources\DateOverrides\Tables;

use App\Models\DateOverride;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DateOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->emptyStateIcon(Heroicon::OutlinedNoSymbol)
            ->emptyStateHeading('Aucun blocage à venir')
            ->emptyStateDescription('Bloquez une période pour vos congés : '
                .'aucun créneau ne sera proposé sur ces journées.')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('D d/m/Y')
                    ->description(fn (DateOverride $record) => $record->date
                        ->isPast() ? 'Passé' : null)
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Plage')
                    ->badge()
                    ->color(fn (DateOverride $record) => $record->isFullDay()
                        ? 'danger'
                        : 'warning')
                    ->formatStateUsing(function (DateOverride $record) {
                        if ($record->isFullDay()) {
                            return 'Journée entière';
                        }

                        return substr((string) $record->start_time, 0, 5)
                            .' – '.substr((string) $record->end_time, 0, 5);
                    }),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->placeholder('–')
                    ->limit(40),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label('À venir uniquement')
                    ->default()
                    ->query(fn (Builder $query) => $query
                        ->whereDate('date', '>=', today())),
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
