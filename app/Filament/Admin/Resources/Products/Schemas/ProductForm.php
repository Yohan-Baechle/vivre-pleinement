<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

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

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Produit')
                        ->icon(Heroicon::OutlinedShoppingBag)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, callable $set, $record) {
                                    if (! $record) {
                                        $set('slug', Str::slug($state));
                                    }
                                })
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->prefix('/produits/')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpanFull(),

                            Textarea::make('short_description')
                                ->label('Description courte')
                                ->helperText('Affichée sur les listes et la fiche produit.')
                                ->rows(3)
                                ->columnSpanFull(),

                            RichEditor::make('description')
                                ->label('Description complète')
                                ->columnSpanFull()
                                ->extraAttributes(['style' => 'min-height: 500px']),
                        ]),

                    Tab::make('Vente')
                        ->icon(Heroicon::OutlinedCurrencyEuro)
                        ->schema([
                            TextInput::make('price')
                                ->label('Prix')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('€')
                                ->required(),

                            Select::make('currency')
                                ->label('Devise')
                                ->options([
                                    'EUR' => 'Euro (€)',
                                    'USD' => 'Dollar ($)',
                                ])
                                ->default('EUR')
                                ->required()
                                ->native(false),

                            Toggle::make('is_active')
                                ->label('Actif')
                                ->default(true)
                                ->onColor('success')
                                ->helperText('Désactivé = produit caché du site public.')
                                ->inline(false)
                                ->columnSpan(2),

                            TextInput::make('stripe_payment_link')
                                ->label('Lien Stripe Payment')
                                ->url()
                                ->placeholder('https://buy.stripe.com/...')
                                ->helperText('Collez ici votre lien Stripe Payment Link.')
                                ->columnSpan(2),
                        ])
                        ->columns(2),

                    Tab::make('Médias')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('cover')
                                ->label('Image de couverture')
                                ->collection('cover')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios(['1:1', '4:3', '16:9', null])
                                ->maxSize(8192)
                                ->helperText('Affichée sur la page produit. Format recommandé : 1:1 (carré).'),

                            SpatieMediaLibraryFileUpload::make('download')
                                ->label('Fichier téléchargeable')
                                ->collection('download')
                                ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                                ->maxSize(51200)
                                ->helperText('PDF ou EPUB. Envoyé au client après achat. Max 50 Mo.'),
                        ]),

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
