<?php

namespace App\Filament\Admin\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
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
            ->columns([
                TextColumn::make('author_name')
                    ->label('Auteur')
                    ->searchable()
                    ->description(fn ($record) => $record->author_email),

                TextColumn::make('content')
                    ->label('Commentaire')
                    ->limit(80)
                    ->wrap()
                    ->html(),

                TextColumn::make('post.title')
                    ->label('Article')
                    ->limit(40)
                    ->url(fn ($record) => $record->post
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
                    ->visible(fn ($record) => $record->status !== CommentStatus::Approved)
                    ->action(fn ($record) => $record->update(['status' => CommentStatus::Approved])),
                Action::make('spam')
                    ->label('Spam')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== CommentStatus::Spam)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => CommentStatus::Spam])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveAll')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => CommentStatus::Approved])),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
