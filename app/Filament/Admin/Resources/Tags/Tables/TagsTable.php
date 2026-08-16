<?php

namespace App\Filament\Admin\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Aucune étiquette')
            ->emptyStateDescription('Les étiquettes affinent le classement '
                .'des articles à l\'intérieur des catégories.')
            ->emptyStateActions([
                CreateAction::make()->label('Créer une étiquette'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Adresse')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Articles')
                    ->sortable()
                    ->badge(),
            ])
            ->defaultSort('name')
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
