<?php

namespace App\Filament\Admin\Resources\Videos\Tables;

use App\Enums\VideoStatus;
use App\Models\Video;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('')
                    ->getStateUsing(fn (Video $record) => $record->thumbnail())
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->imageWidth(120)
                    ->imageHeight(68),

                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap()
                    ->description(fn (Video $record) => 'YouTube ID : '.$record->youtube_id),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (Video $record) => match (true) {
                        $record->is_missing => '⚠️ Manquante',
                        $record->isShort() => 'Short',
                        default => $record->status->getLabel(),
                    })
                    ->color(fn (Video $record) => match (true) {
                        $record->is_missing => 'danger',
                        $record->isShort() => 'warning',
                        default => $record->status->getColor(),
                    }),

                TextColumn::make('view_count')
                    ->label('Vues')
                    ->numeric(thousandsSeparator: ' ')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('duration_seconds')
                    ->label('Durée')
                    ->formatStateUsing(fn (Video $record) => $record->durationFormatted() ?? '–')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('editorial')
                    ->label('Éditorial')
                    ->badge()
                    ->getStateUsing(fn (Video $record) => match (true) {
                        $record->isEnriched() && $record->hasTranscript() => '✓ Complet',
                        $record->isEnriched() => 'Sans transcription',
                        $record->hasTranscript() => 'À enrichir',
                        default => '⚠️ À traiter',
                    })
                    ->color(fn (Video $record) => match (true) {
                        $record->isEnriched() && $record->hasTranscript() => 'success',
                        $record->isEnriched() || $record->hasTranscript() => 'warning',
                        default => 'danger',
                    })
                    ->tooltip(fn (Video $record) => sprintf(
                        'Intro : %s · Résumé : %s · Transcription : %s',
                        filled($record->intro) ? 'oui' : 'non',
                        filled($record->summary) ? 'oui' : 'non',
                        $record->hasTranscript() ? 'oui' : 'non',
                    )),

                TextColumn::make('categories.name')
                    ->label('Catégories')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Publiée le')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('synced_at')
                    ->label('Sync')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('has_locked_fields')
                    ->label('Verrous')
                    ->getStateUsing(fn (Video $record) => ! empty($record->sync_locked_fields))
                    ->boolean()
                    ->tooltip(fn (Video $record) => empty($record->sync_locked_fields)
                        ? 'Aucun champ verrouillé'
                        : 'Verrouillés : '.implode(', ', $record->sync_locked_fields))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(VideoStatus::class),

                Filter::make('is_missing')
                    ->label('Manquantes sur YouTube')
                    ->query(fn (Builder $query) => $query->where('is_missing', true))
                    ->toggle(),

                Filter::make('shorts')
                    ->label('Shorts uniquement')
                    ->query(fn (Builder $query) => $query->where('duration_seconds', '<=', Video::SHORT_DURATION_THRESHOLD))
                    ->toggle(),

                SelectFilter::make('editorial')
                    ->label('État éditorial')
                    ->options([
                        'to_enrich' => 'À enrichir (sans intro/résumé)',
                        'no_transcript' => 'Sans transcription',
                        'complete' => 'Complètes',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'to_enrich' => $query->where(fn (Builder $q) => $q
                                ->whereNull('intro')->orWhere('intro', '')
                                ->orWhereNull('summary')->orWhere('summary', '')),
                            'no_transcript' => $query->where(fn (Builder $q) => $q
                                ->whereNull('transcript')->orWhere('transcript', '')),
                            'complete' => $query
                                ->whereNotNull('intro')->where('intro', '!=', '')
                                ->whereNotNull('summary')->where('summary', '!=', '')
                                ->whereNotNull('transcript')->where('transcript', '!=', ''),
                            default => $query,
                        };
                    }),

                SelectFilter::make('categories')
                    ->label('Catégorie')
                    ->relationship('categories', 'name')
                    ->preload(),

                TrashedFilter::make(),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Video $record) => route('videos.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Video $record) => ! $record->is_missing && $record->status === VideoStatus::Published && ! $record->isShort()),

                Action::make('open_youtube')
                    ->label('YouTube')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Video $record) => $record->youtubeUrl())
                    ->openUrlInNewTab(),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publier')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = $records->each(fn (Video $v) => $v->update(['status' => VideoStatus::Published]))->count();
                            Notification::make()
                                ->title("{$count} vidéo(s) publiée(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish')
                        ->label('Masquer')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = $records->each(fn (Video $v) => $v->update(['status' => VideoStatus::Draft]))->count();
                            Notification::make()
                                ->title("{$count} vidéo(s) masquée(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('lock_content')
                        ->label('Verrouiller titre/description')
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalDescription('Protège le titre, la description et la miniature contre la prochaine synchronisation YouTube. Utile après une réécriture manuelle.')
                        ->action(function (Collection $records): void {
                            $count = $records->each(function (Video $v): void {
                                $locked = array_values(array_unique(array_merge(
                                    $v->sync_locked_fields ?? [],
                                    ['title', 'description', 'thumbnail_url'],
                                )));
                                $v->update(['sync_locked_fields' => $locked]);
                            })->count();

                            Notification::make()
                                ->title("{$count} vidéo(s) verrouillée(s) contre la sync")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
