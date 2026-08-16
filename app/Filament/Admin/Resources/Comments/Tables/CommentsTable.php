<?php

namespace App\Filament\Admin\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->emptyStateHeading('Aucun commentaire')
            ->emptyStateDescription('Les commentaires laissés sur le blog '
                .'arrivent ici pour être approuvés avant publication.')
            ->columns([
                TextColumn::make('author_name')
                    ->label('Auteur')
                    ->searchable()
                    ->description(fn (Comment $record) => $record->author_email),

                TextColumn::make('content')
                    ->label('Commentaire')
                    ->limit(80)
                    ->wrap()
                    ->tooltip(fn (Comment $record) => $record->content),

                TextColumn::make('post.title')
                    ->label('Article')
                    ->limit(40)
                    ->url(fn (Comment $record) => $record->post
                        ? route('filament.admin.resources.posts.edit', $record->post)
                        : null)
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('posted_at')
                    ->label('Posté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('posted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CommentStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::Approved)
                    ->action(function (Comment $record): void {
                        $record->update(['status' => CommentStatus::Approved]);

                        Notification::make()
                            ->success()
                            ->title('Commentaire publié')
                            ->body('Il est désormais visible sous l\'article.')
                            ->send();
                    }),
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lire en entier'),
                    Action::make('spam')
                        ->label('Marquer comme spam')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (Comment $record) => $record->status !== CommentStatus::Spam)
                        ->requiresConfirmation()
                        ->action(function (Comment $record): void {
                            $record->update(['status' => CommentStatus::Spam]);

                            Notification::make()
                                ->success()
                                ->title('Commentaire marqué comme spam')
                                ->send();
                        }),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveAll')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Les commentaires sélectionnés '
                            .'apparaîtront sous leur article.')
                        ->action(function (Collection $records): void {
                            $records->each(fn (Comment $comment) => $comment->update([
                                'status' => CommentStatus::Approved,
                            ]));

                            Notification::make()
                                ->success()
                                ->title(trans_choice(
                                    '{1} :count commentaire publié'
                                        .'|]1,*[ :count commentaires publiés',
                                    $records->count(),
                                    ['count' => $records->count()],
                                ))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
