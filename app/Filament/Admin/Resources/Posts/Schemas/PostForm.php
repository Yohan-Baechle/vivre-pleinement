<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Tag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Contenu')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->schema([
                            TextInput::make('title')
                                ->label('Titre')
                                ->placeholder('Titre de l\'article')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, callable $set, $record) {
                                    if (! $record) {
                                        $set('slug', Str::slug($state));
                                    }
                                })
                                ->extraAttributes(['class' => 'text-xl'])
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->prefix('/')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Textarea::make('excerpt')
                                ->label('Extrait')
                                ->helperText('Résumé court affiché dans les listes (optionnel).')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),

                            RichEditor::make('content')
                                ->label('Contenu')
                                ->resizableImages()
                                ->columnSpanFull()
                                ->extraAttributes(['style' => 'min-height: 600px']),
                        ]),

                    Tab::make('Publication')
                        ->icon(Heroicon::OutlinedRocketLaunch)
                        ->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(PostStatus::class)
                                ->default(PostStatus::Draft)
                                ->required()
                                ->native(false),

                            DateTimePicker::make('published_at')
                                ->label('Date de publication')
                                ->seconds(false)
                                ->default(now())
                                ->native(false),

                            SpatieMediaLibraryFileUpload::make('featured')
                                ->label('Image à la une')
                                ->collection('featured')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios(['16:9', '4:3', '1:1', null])
                                ->maxSize(8192)
                                ->helperText('Formats : JPG, PNG, WebP. Max 8 Mo.')
                                ->columnSpanFull(),

                            Select::make('categories')
                                ->label('Catégories')
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->options(fn () => Category::orderBy('name')->pluck('name', 'id')),

                            Select::make('tags')
                                ->label('Étiquettes')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->createOptionForm([
                                    TextInput::make('name')->required(),
                                    TextInput::make('slug')->required(),
                                ])
                                ->options(fn () => Tag::orderBy('name')->pluck('name', 'id')),

                            Toggle::make('comments_enabled')
                                ->label('Commentaires ouverts')
                                ->default(true)
                                ->onColor('success')
                                ->helperText('Désactivé = les visiteurs ne peuvent plus commenter cet article.')
                                ->inline(false)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('SEO')
                        ->icon(Heroicon::OutlinedMagnifyingGlass)
                        ->schema([
                            TextInput::make('seo_title')
                                ->label('Titre SEO')
                                ->maxLength(255)
                                ->helperText('Idéalement 50-60 caractères. Laisse vide pour utiliser le titre de l\'article.')
                                ->live(debounce: 500)
                                ->columnSpanFull(),

                            Textarea::make('seo_description')
                                ->label('Description SEO')
                                ->maxLength(320)
                                ->rows(3)
                                ->helperText('Idéalement 150-160 caractères.')
                                ->live(debounce: 500)
                                ->columnSpanFull(),

                            TextInput::make('seo_canonical')
                                ->label('URL canonique')
                                ->url()
                                ->placeholder('https://...')
                                ->helperText('À renseigner uniquement si cet article est une copie d\'un autre.')
                                ->columnSpanFull(),

                            TextInput::make('seo_robots')
                                ->label('Robots')
                                ->placeholder('index, follow')
                                ->helperText('Vide = index, follow par défaut.'),
                        ])
                        ->columns(2),
                ]),
        ]);
    }
}
