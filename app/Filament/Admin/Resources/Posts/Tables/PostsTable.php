<?php

namespace App\Filament\Admin\Resources\Posts\Tables;

use App\Enums\PostStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')
                    ->collection('featured')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('categories.name')
                    ->badge()
                    ->separator(', ')
                    ->limit(40),

                TextColumn::make('mesh_status')
                    ->label('Maillage')
                    ->badge()
                    ->state(fn ($record): string => $record->meshStatus())
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pillar' => 'Pilier',
                        'meshed' => 'Maillé',
                        'orphan' => 'Orphelin',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pillar' => 'heroicon-m-star',
                        'meshed' => 'heroicon-m-check-circle',
                        'orphan' => 'heroicon-m-exclamation-triangle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pillar' => 'warning',
                        'meshed' => 'success',
                        'orphan' => 'danger',
                    }),

                TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('💬')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PostStatus::class),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('view_on_site')
                        ->label('Voir sur le site')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(fn ($record) => url('/'.$record->slug))
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => $record->status === PostStatus::Published),
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
