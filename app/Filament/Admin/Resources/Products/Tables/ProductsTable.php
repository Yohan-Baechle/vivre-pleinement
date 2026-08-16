<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading('Aucun produit')
            ->emptyStateDescription('Un produit est un fichier vendu au '
                .'téléchargement, hors formations.')
            ->emptyStateActions([
                CreateAction::make()->label('Créer un produit'),
            ])
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')
                    ->label('')
                    ->square()
                    ->size(50),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price_cents')
                    ->label('Prix')
                    ->money('EUR', divideBy: 100)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                TextColumn::make('stripe_payment_link')
                    ->label('Paiement')
                    ->placeholder('–')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? 'Ouvrir dans Stripe'
                        : '–')
                    ->color('primary')
                    ->url(fn (?string $state) => $state)
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('En vente')
                    ->placeholder('Tous les produits')
                    ->trueLabel('En vente uniquement')
                    ->falseLabel('Retirés de la vente'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
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
