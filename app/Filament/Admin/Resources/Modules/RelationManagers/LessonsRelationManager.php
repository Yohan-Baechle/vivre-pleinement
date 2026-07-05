<?php

namespace App\Filament\Admin\Resources\Modules\RelationManagers;

use App\Support\VideoEmbed;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Leçons';

    protected static ?string $modelLabel = 'leçon';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Titre de la leçon')
                    ->placeholder('Ex. : La respiration apaisante')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, callable $set, $record): void {
                        if (! $record) {
                            $set('slug', Str::slug($state).'-'.Str::lower(Str::random(5)));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('video_id')
                    ->label('Vidéo')
                    ->placeholder('Collez le lien YouTube ou Vimeo')
                    ->helperText('Collez l\'adresse complète de la vidéo : la plateforme est détectée automatiquement.')
                    ->prefixIcon('heroicon-o-play')
                    ->maxLength(500)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set): void {
                        $parsed = VideoEmbed::parse($state);
                        $set('video_provider', $parsed['provider']);
                        $set('video_id', $parsed['id']);
                    })
                    ->columnSpanFull(),

                Select::make('video_provider')
                    ->options([
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                    ])
                    ->dehydrated()
                    ->hidden(),

                RichEditor::make('content')
                    ->label('Texte de la leçon')
                    ->helperText('Affiché sous la vidéo : consignes, exercices, ressources…')
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'min-height: 320px']),

                Toggle::make('is_free_preview')
                    ->label('Leçon offerte en aperçu')
                    ->helperText('Visible gratuitement sur la page de vente, sans achat.')
                    ->onColor('success')
                    ->inline(false),

                TextInput::make('duration_seconds')
                    ->label('Durée (en secondes)')
                    ->placeholder('Optionnel')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('s'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->reorderable('position')
            ->reorderRecordsTriggerAction(fn ($action) => $action->label('Réorganiser')->button())
            ->emptyStateHeading('Aucune leçon')
            ->emptyStateDescription('Ajoutez votre première leçon pour ce module.')
            ->emptyStateIcon('heroicon-o-play-circle')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->state(fn ($record, $livewire) => $livewire->getTableRecords()->search(fn ($r) => $r->is($record)) + 1)
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Leçon')
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->is_free_preview ? 'Aperçu gratuit' : null),
                IconColumn::make('video_id')
                    ->label('Vidéo')
                    ->boolean()
                    ->state(fn ($record): bool => filled($record->video_id)),
                TextColumn::make('duration_seconds')
                    ->label('Durée')
                    ->formatStateUsing(fn (?int $state): string => $state ? gmdate('i:s', $state) : '–')
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une leçon')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Nouvelle leçon')
                    ->modalWidth(Width::TwoExtraLarge),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Modifier la leçon')
                    ->modalWidth(Width::TwoExtraLarge),
                DeleteAction::make()
                    ->iconButton(),
            ]);
    }
}
