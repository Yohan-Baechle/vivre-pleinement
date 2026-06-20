<?php

namespace App\Filament\Admin\Resources\DateOverrides\Tables;

use App\Models\DateOverride;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DateOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Plage')
                    ->formatStateUsing(function (DateOverride $record) {
                        if ($record->isFullDay()) {
                            return 'Journée entière';
                        }

                        return substr((string) $record->start_time, 0, 5).' – '.substr((string) $record->end_time, 0, 5);
                    }),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->placeholder('–')
                    ->limit(40),
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
