<?php

namespace App\Filament\Admin\Resources\Videos\Schemas;

use App\Enums\VideoStatus;
use App\Models\Category;
use App\Models\Video;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('⚠️ Vidéo manquante sur YouTube')
                ->description('Cette vidéo a disparu de la chaîne YouTube (supprimée ou passée en privée). Elle est automatiquement masquée du site public.')
                ->visible(fn ($record) => $record?->is_missing === true)
                ->extraAttributes(['class' => 'border-warning-300 bg-warning-50 ring-warning-300'])
                ->schema([])
                ->columnSpanFull(),

            Tabs::make()
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Contenu')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->schema([
                            Placeholder::make('preview')
                                ->label('Sur YouTube')
                                ->content(fn ($record): HtmlString => $record
                                    ? new HtmlString(sprintf(
                                        '<div class="flex items-center gap-4">'
                                        .'<img src="%s" alt="" class="h-20 w-32 rounded-lg object-cover ring-1 ring-gray-200">'
                                        .'<div>'
                                        .'<a href="%s" target="_blank" rel="noopener noreferrer" class="text-primary-600 font-mono text-sm hover:underline">%s</a>'
                                        .'<p class="mt-1 text-xs text-gray-500">Cliquez pour voir la vidéo sur YouTube</p>'
                                        .'</div></div>',
                                        e($record->thumbnail()),
                                        e($record->youtubeUrl()),
                                        e($record->youtube_id),
                                    ))
                                    : new HtmlString('–')
                                )
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull()
                                ->hintAction(self::lockToggleAction('title')),

                            TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->prefix('/videos/')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->helperText('Modifier le slug change l\'URL publique de la vidéo.')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description')
                                ->rows(6)
                                ->columnSpanFull()
                                ->hintAction(self::lockToggleAction('description')),

                            TextInput::make('thumbnail_url')
                                ->label('URL de la miniature')
                                ->url()
                                ->maxLength(500)
                                ->helperText('Personnalisez si besoin (par défaut : la miniature YouTube).')
                                ->columnSpanFull()
                                ->hintAction(self::lockToggleAction('thumbnail_url')),
                        ])
                        ->columns(2),

                    Tab::make('SEO & Contenu éditorial')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->badge(fn ($record) => match (true) {
                            $record === null => null,
                            $record->isEnriched() && $record->hasTranscript() => 'Complet',
                            $record->isEnriched() || $record->hasTranscript() => 'Partiel',
                            default => 'À faire',
                        })
                        ->badgeColor(fn ($record) => match (true) {
                            $record === null => 'gray',
                            $record->isEnriched() && $record->hasTranscript() => 'success',
                            $record->isEnriched() || $record->hasTranscript() => 'warning',
                            default => 'danger',
                        })
                        ->schema([
                            Placeholder::make('seo_help')
                                ->hiddenLabel()
                                ->content(fn ($record) => new HtmlString(self::editorialChecklist($record)))
                                ->columnSpanFull(),

                            TextInput::make('seo_description')
                                ->label('Meta description (SEO)')
                                ->helperText('Affichée dans les résultats Google. 150-160 caractères idéalement. Si vide, fallback sur le résumé puis la description YouTube tronquée.')
                                ->maxLength(320)
                                ->columnSpanFull(),

                            Textarea::make('summary')
                                ->label('Résumé d\'introduction')
                                ->helperText('2-4 phrases affichées en intro sur la page publique. Contenu unique = signal SEO.')
                                ->rows(3)
                                ->columnSpanFull(),

                            RichEditor::make('intro')
                                ->label('Texte d\'introduction (au-dessus de la vidéo)')
                                ->helperText('Paragraphe(s) affichés AVANT la vidéo. C\'est le texte le plus important pour Google : il pose le sujet avec les mots-clés que les gens recherchent.')
                                ->toolbarButtons([
                                    ['bold', 'italic'],
                                    ['link'],
                                    ['undo', 'redo'],
                                ])
                                ->columnSpanFull(),

                            Repeater::make('key_takeaways')
                                ->label('Points clés à retenir')
                                ->helperText('3 à 7 idées principales de la vidéo. Affichées en liste sous l\'embed. Excellent pour le SEO et la rétention.')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Titre du point')
                                        ->required()
                                        ->maxLength(120),
                                    Textarea::make('content')
                                        ->label('Détail (optionnel)')
                                        ->rows(2)
                                        ->maxLength(500),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                ->collapsible()
                                ->collapsed()
                                ->reorderableWithButtons()
                                ->addActionLabel('Ajouter un point clé')
                                ->defaultItems(0)
                                ->maxItems(10)
                                ->columnSpanFull(),

                            Repeater::make('chapters')
                                ->label('Chapitres (timestamps)')
                                ->helperText('Pour activer les "key moments" de Google Search. Le premier chapitre doit commencer à 0 seconde.')
                                ->schema([
                                    TextInput::make('start_seconds')
                                        ->label('Début (secondes)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->suffix('s'),
                                    TextInput::make('title')
                                        ->label('Titre du chapitre')
                                        ->required()
                                        ->maxLength(120),
                                ])
                                ->columns(2)
                                ->itemLabel(fn (array $state): ?string => isset($state['start_seconds'], $state['title'])
                                    ? sprintf('%s – %s', self::formatSeconds((int) $state['start_seconds']), $state['title'])
                                    : null)
                                ->collapsible()
                                ->collapsed()
                                ->reorderableWithButtons()
                                ->orderColumn('start_seconds')
                                ->addActionLabel('Ajouter un chapitre')
                                ->defaultItems(0)
                                ->columnSpanFull(),

                            RichEditor::make('transcript')
                                ->label('Transcription / article éditorial')
                                ->helperText('Contenu indexable par Google. Idéalement 300+ mots. Peut être une transcription nettoyée, un article complémentaire, ou les deux.')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike'],
                                    ['h2', 'h3', 'blockquote'],
                                    ['bulletList', 'orderedList'],
                                    ['link'],
                                    ['undo', 'redo'],
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('Publication')
                        ->icon(Heroicon::OutlinedRocketLaunch)
                        ->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(VideoStatus::class)
                                ->default(VideoStatus::Published)
                                ->required()
                                ->native(false),

                            DateTimePicker::make('published_at')
                                ->label('Publiée sur le site le')
                                ->seconds(false)
                                ->native(false)
                                ->helperText('Date à laquelle la vidéo apparaît sur le site.'),

                            Placeholder::make('youtube_published_at_display')
                                ->label('Publiée sur YouTube le')
                                ->content(fn ($record) => $record?->youtube_published_at?->locale('fr')->isoFormat('D MMMM YYYY [à] HH:mm') ?? '–'),

                            Placeholder::make('synced_at_display')
                                ->label('Dernière synchronisation')
                                ->content(fn ($record) => $record?->synced_at?->diffForHumans() ?? 'Jamais synchronisée'),

                            CheckboxList::make('categories')
                                ->label('Catégories')
                                ->relationship('categories', 'name')
                                ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                                ->columns(2)
                                ->columnSpanFull(),

                            Select::make('related_post_id')
                                ->label('Article de blog associé')
                                ->relationship('relatedPost', 'title')
                                ->searchable()
                                ->preload()
                                ->helperText('Maillage interne : affiche un bloc « À lire aussi » sur la vidéo et un bloc vidéo sur l\'article. Détecté automatiquement depuis le lien dans la description YouTube.')
                                ->columnSpanFull(),

                            Checkbox::make('is_missing')
                                ->label('Marquée comme manquante')
                                ->helperText('Cochée automatiquement par le sync quand la vidéo disparaît de YouTube. Décocher manuellement uniquement si vous êtes sûr.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('Statistiques')
                        ->icon(Heroicon::OutlinedChartBar)
                        ->schema([
                            Placeholder::make('view_count_display')
                                ->label('Vues')
                                ->content(fn ($record) => $record?->view_count
                                    ? number_format($record->view_count, 0, ',', ' ')
                                    : '–'),

                            Placeholder::make('like_count_display')
                                ->label('Likes')
                                ->content(fn ($record) => $record?->like_count
                                    ? number_format($record->like_count, 0, ',', ' ')
                                    : '–'),

                            Placeholder::make('duration_display')
                                ->label('Durée')
                                ->content(fn ($record) => $record?->durationFormatted() ?? '–'),

                            Placeholder::make('locked_fields_display')
                                ->label('Champs verrouillés contre la sync')
                                ->content(function ($record) {
                                    $locked = $record?->sync_locked_fields ?? [];

                                    return empty($locked)
                                        ? 'Aucun (tous les champs seront écrasés à la prochaine sync)'
                                        : implode(', ', $locked);
                                }),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    /**
     * Liste de complétude éditoriale : montre d'un coup d'œil ce qui est fait
     * et ce qui reste, pour guider la rédaction directement dans l'admin.
     */
    private static function editorialChecklist(?Video $record): string
    {
        $row = function (bool $done, string $label): string {
            $icon = $done
                ? '<span class="text-success-600">✓</span>'
                : '<span class="text-danger-500">○</span>';
            $class = $done ? 'text-gray-600' : 'font-medium text-gray-900';

            return '<li class="flex items-center gap-2 '.$class.'">'.$icon.' '.$label.'</li>';
        };

        $items = $record
            ? implode('', [
                $row(filled($record->intro), 'Introduction (texte au-dessus de la vidéo)'),
                $row(filled($record->summary), 'Résumé court'),
                $row(filled($record->seo_description), 'Meta description SEO'),
                $row(! empty($record->key_takeaways), 'Points clés à retenir'),
                $row(filled($record->transcript), 'Transcription'),
            ])
            : '';

        return '<div class="bg-primary-50 text-primary-900 ring-primary-200 rounded-lg p-4 text-sm ring-1">'
            .'<p class="font-medium">Complétude éditoriale</p>'
            .'<p class="mt-1 text-xs text-primary-700">Plus la page est riche en contenu unique, mieux elle se positionne sur Google.</p>'
            .'<ul class="mt-3 space-y-1.5">'.$items.'</ul>'
            .'</div>';
    }

    /**
     * Action de verrouillage pour les champs sync-sensibles.
     */
    private static function lockToggleAction(string $field): Action
    {
        return Action::make('lock_'.$field)
            ->icon(fn ($record) => $record?->isLocked($field) ? Heroicon::SolidLockClosed : Heroicon::OutlinedLockOpen)
            ->color(fn ($record) => $record?->isLocked($field) ? 'danger' : 'gray')
            ->tooltip(fn ($record) => $record?->isLocked($field)
                ? 'Verrouillé – le sync n\'écrasera pas ce champ'
                : 'Cliquer pour verrouiller (le sync ne touchera plus à ce champ)'
            )
            ->action(function ($record) use ($field): void {
                if (! $record) {
                    return;
                }
                $locked = $record->sync_locked_fields ?? [];
                if (in_array($field, $locked, true)) {
                    $locked = array_values(array_diff($locked, [$field]));
                } else {
                    $locked[] = $field;
                }
                $record->update(['sync_locked_fields' => $locked]);
            });
    }

    private static function formatSeconds(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }
}
