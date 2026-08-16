<?php

namespace App\Filament\Admin\Resources\Students\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun élève inscrit')
            ->emptyStateDescription('Les comptes créés lors de l\'achat d\'une '
                .'formation apparaissent ici.')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('enrollments_count')
                    ->label('Formations')
                    ->counts('enrollments')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('anonymized_at')
                    ->label('Anonymisé')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Oui' : '')
                    ->color('danger')
                    ->placeholder(''),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
