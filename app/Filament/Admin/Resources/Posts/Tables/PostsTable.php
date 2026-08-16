<?php

namespace App\Filament\Admin\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->emptyStateHeading('Aucun article pour l\'instant')
            ->emptyStateDescription('Écrivez votre premier article : il '
                .'apparaîtra sur le blog dès que vous le publierez.')
            ->emptyStateActions([
                CreateAction::make()->label('Écrire un article'),
            ])
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')
                    ->collection('featured')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('categories.name')
                    ->label('Catégories')
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
                    ->label('Commentaires')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PostStatus::class),
                SelectFilter::make('categories')
                    ->label('Catégorie')
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
                        ->url(fn (Post $record) => route('blog.show', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Post $record) => $record->status === PostStatus::Published),
                    Action::make('preview_draft')
                        ->label('Prévisualiser le brouillon')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn (Post $record) => $record->previewUrl())
                        ->openUrlInNewTab()
                        ->visible(fn (Post $record) => $record->status !== PostStatus::Published),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publier')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Les articles sélectionnés '
                            .'deviennent visibles sur le blog.')
                        ->action(fn (Collection $records) => self::changeStatus(
                            $records,
                            PostStatus::Published,
                            '{1} :count article publié|[2,*] :count articles publiés',
                        ))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish')
                        ->label('Repasser en brouillon')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('Les articles sélectionnés '
                            .'disparaissent du blog mais restent modifiables.')
                        ->action(fn (Collection $records) => self::changeStatus(
                            $records,
                            PostStatus::Draft,
                            '{1} :count article repassé en brouillon'
                                .'|[2,*] :count articles repassés en brouillon',
                        ))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Change le statut enregistrement par enregistrement pour que
     * l'observateur d'articles (sitemap, IndexNow, caches) reste déclenché.
     *
     * @param  Collection<int, Post>  $records
     */
    private static function changeStatus(
        Collection $records,
        PostStatus $status,
        string $message,
    ): void {
        $records->each(function (Post $post) use ($status): void {
            $post->update([
                'status' => $status,
                'published_at' => $status === PostStatus::Published
                    ? ($post->published_at ?? now())
                    : $post->published_at,
            ]);
        });

        Notification::make()
            ->success()
            ->title(trans_choice(
                $message,
                $records->count(),
                ['count' => $records->count()],
            ))
            ->send();
    }
}
