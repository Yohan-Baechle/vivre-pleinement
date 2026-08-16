<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
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
                                ->label('Adresse de la page')
                                ->prefix('/blog/')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->helperText(fn (?Post $record): ?string => $record?->status === PostStatus::Published
                                    ? 'Cet article est en ligne : si vous changez '
                                        .'son adresse, l\'ancienne sera redirigée '
                                        .'automatiquement vers la nouvelle.'
                                    : null)
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
                            Text::make(fn (Get $get): Htmlable => self::serpPreview($get))
                                ->columnSpanFull(),

                            TextInput::make('seo_title')
                                ->label('Titre SEO')
                                ->maxLength(255)
                                ->placeholder(fn (Get $get) => $get('title'))
                                ->helperText(fn (?string $state, Get $get): string => self::lengthHint(
                                    filled($state) ? $state : (string) $get('title'),
                                    50,
                                    60,
                                    'Vide, Google reprend le titre de l\'article.',
                                ))
                                ->live(debounce: 500)
                                ->columnSpanFull(),

                            Textarea::make('seo_description')
                                ->label('Description SEO')
                                ->maxLength(320)
                                ->rows(3)
                                ->helperText(fn (?string $state): string => self::lengthHint(
                                    (string) $state,
                                    150,
                                    160,
                                    'Vide, Google choisit lui-même un extrait de l\'article.',
                                ))
                                ->live(debounce: 500)
                                ->columnSpanFull(),

                            TextInput::make('seo_canonical')
                                ->label('URL canonique')
                                ->url()
                                ->placeholder('https://...')
                                ->helperText('À renseigner uniquement si cet article est une copie d\'un autre.')
                                ->columnSpanFull(),

                            Select::make('seo_robots')
                                ->label('Visibilité dans les moteurs')
                                ->options(fn (?Post $record) => self::robotsOptions($record))
                                ->placeholder('Visible dans Google (par défaut)')
                                ->native(false)
                                ->helperText('« Masqué » retire la page de Google '
                                    .'tout en gardant ses liens actifs.'),

                            Repeater::make('faq')
                                ->label('FAQ (questions fréquentes)')
                                ->helperText('Affichées en accordéon sous l\'article et exposées à Google en FAQPage (rich results). 3 à 5 questions maximum, réponses courtes et auto-suffisantes.')
                                ->schema([
                                    TextInput::make('question')
                                        ->label('Question')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('answer')
                                        ->label('Réponse')
                                        ->required()
                                        ->rows(3),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                ->defaultItems(0)
                                ->reorderable()
                                ->collapsible()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    /**
     * Aperçu du résultat Google, seule façon de rendre concrets les compteurs
     * de caractères : l'admin voit ce que verra l'internaute.
     */
    private static function serpPreview(Get $get): Htmlable
    {
        $title = filled($get('seo_title'))
            ? (string) $get('seo_title')
            : (string) ($get('title') ?: 'Titre de l\'article');

        $description = filled($get('seo_description'))
            ? (string) $get('seo_description')
            : 'Ajoutez une description SEO pour maîtriser l\'extrait affiché '
                .'sous le titre.';

        $url = config('app.url').'/blog/'.($get('slug') ?: 'mon-article');

        return new HtmlString(sprintf(
            '<div style="border:1px solid rgb(var(--gray-200));border-radius:.5rem;'
                .'padding:.9rem 1rem;background:rgb(var(--gray-50))">'
                .'<div style="font-size:.75rem;color:#3c4043">%s</div>'
                .'<div style="font-size:1.1rem;color:#1a0dab;line-height:1.3;'
                .'margin:.15rem 0 .2rem">%s</div>'
                .'<div style="font-size:.82rem;color:#4d5156;line-height:1.45">%s</div>'
                .'</div>',
            e(Str::limit($url, 90)),
            e(Str::limit($title, 65)),
            e(Str::limit($description, 170)),
        ));
    }

    /**
     * Compteur de caractères qui dit aussi ce qu'il faut en faire, plutôt
     * qu'une fourchette théorique que rien ne vérifie.
     */
    private static function lengthHint(
        string $value,
        int $min,
        int $max,
        string $whenEmpty,
    ): string {
        $length = mb_strlen(trim($value));

        if ($length === 0) {
            return $whenEmpty;
        }

        return match (true) {
            $length < $min => "{$length} caractères — un peu court, visez {$min} à {$max}.",
            $length > $max => "{$length} caractères — Google coupera après {$max}.",
            default => "{$length} caractères — longueur idéale.",
        };
    }

    /**
     * Deux choix explicites, plus la valeur existante si elle sort de ces
     * deux cas : jamais de directive écrasée en silence à l'enregistrement.
     *
     * @return array<string, string>
     */
    private static function robotsOptions(?Post $record): array
    {
        $options = [
            Post::ROBOTS_INDEXED => 'Visible dans Google (recommandé)',
            Post::ROBOTS_HIDDEN => 'Masqué : ne pas indexer cette page',
        ];

        $current = $record?->seo_robots;

        if (filled($current) && ! array_key_exists($current, $options)) {
            $options[$current] = 'Réglage personnalisé : '.$current;
        }

        return $options;
    }
}
