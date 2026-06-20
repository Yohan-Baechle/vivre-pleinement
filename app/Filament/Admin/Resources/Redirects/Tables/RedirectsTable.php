<?php

namespace App\Filament\Admin\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
