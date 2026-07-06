<?php

namespace App\Filament\Admin\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Inscriptions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('course.title')
                    ->label('Formation'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('amount_paid_cents')
                    ->label('Montant')
                    ->money('eur', divideBy: 100),
                TextColumn::make('purchased_at')
                    ->label('Acheté le')
                    ->dateTime('d/m/Y')
                    ->placeholder('–'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
