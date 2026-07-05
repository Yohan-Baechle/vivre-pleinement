<?php

namespace App\Filament\Admin\Resources\Courses\Schemas;

use App\Enums\CourseStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Présentation')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->schema([
                            TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, callable $set, $record): void {
                                    if (! $record) {
                                        $set('slug', Str::slug($state));
                                    }
                                })
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->prefix('/formations/')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->columnSpanFull(),

                            TextInput::make('subtitle')
                                ->label('Sous-titre')
                                ->helperText('Phrase d\'accroche affichée sous le titre.')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            RichEditor::make('description')
                                ->label('Description')
                                ->columnSpanFull()
                                ->extraAttributes(['style' => 'min-height: 320px']),

                            Repeater::make('outcomes')
                                ->label('Ce que vous allez apprendre')
                                ->helperText('Les bénéfices clés de la formation, affichés en liste sur la page de vente.')
                                ->simple(
                                    TextInput::make('value')
                                        ->required()
                                        ->maxLength(255),
                                )
                                ->addActionLabel('Ajouter un objectif')
                                ->defaultItems(0)
                                ->columnSpanFull(),

                            TextInput::make('level')
                                ->label('Niveau')
                                ->placeholder('Débutant, Tous niveaux…')
                                ->maxLength(255),

                            TextInput::make('duration_minutes')
                                ->label('Durée totale (minutes)')
                                ->numeric()
                                ->minValue(0),
                        ])
                        ->columns(2),

                    Tab::make('Vente & médias')
                        ->icon(Heroicon::OutlinedCurrencyEuro)
                        ->schema([
                            TextInput::make('price')
                                ->label('Prix')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->suffix('€')
                                ->required(),

                            Select::make('currency')
                                ->label('Devise')
                                ->options(['EUR' => 'Euro (€)'])
                                ->default('EUR')
                                ->required()
                                ->native(false),

                            SpatieMediaLibraryFileUpload::make('cover')
                                ->label('Image de couverture')
                                ->collection('cover')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios(['16:9', '4:3', null])
                                ->maxSize(8192)
                                ->helperText('Affichée sur le catalogue et la page de vente. Format recommandé : 16:9.')
                                ->columnSpanFull(),

                            Select::make('intro_video_provider')
                                ->label('Vidéo de présentation — plateforme')
                                ->options([
                                    'youtube' => 'YouTube',
                                    'vimeo' => 'Vimeo',
                                ])
                                ->native(false)
                                ->placeholder('Aucune'),

                            TextInput::make('intro_video_id')
                                ->label('Identifiant de la vidéo')
                                ->helperText('Pour YouTube : l\'ID après ?v= (ex. dQw4w9WgXcQ). Pour Vimeo : l\'ID numérique.')
                                ->maxLength(255),
                        ])
                        ->columns(2),

                    Tab::make('Publication')
                        ->icon(Heroicon::OutlinedRocketLaunch)
                        ->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(CourseStatus::class)
                                ->default(CourseStatus::Draft)
                                ->required()
                                ->native(false),

                            DateTimePicker::make('published_at')
                                ->label('Publiée le')
                                ->seconds(false)
                                ->native(false)
                                ->helperText('La formation n\'apparaît sur le catalogue qu\'une fois publiée et la date passée.'),

                            TextInput::make('position')
                                ->label('Ordre d\'affichage')
                                ->numeric()
                                ->default(0)
                                ->helperText('Plus le nombre est petit, plus la formation apparaît tôt dans le catalogue.'),
                        ])
                        ->columns(2),

                    Tab::make('SEO')
                        ->icon(Heroicon::OutlinedMagnifyingGlass)
                        ->schema([
                            TextInput::make('seo_title')
                                ->label('Titre SEO')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Textarea::make('seo_description')
                                ->label('Description SEO')
                                ->maxLength(320)
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
