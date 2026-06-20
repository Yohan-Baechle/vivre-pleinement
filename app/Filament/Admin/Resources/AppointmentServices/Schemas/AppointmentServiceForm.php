<?php

namespace App\Filament\Admin\Resources\AppointmentServices\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AppointmentServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Prestation')
                ->columns(2)
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
                        ->prefix('/reservation/')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->helperText('Affichée au visiteur lors du choix de la prestation.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Durée & tarif')
                ->columns(2)
                ->schema([
                    TextInput::make('duration_minutes')
                        ->label('Durée (minutes)')
                        ->numeric()
                        ->minValue(5)
                        ->step(5)
                        ->default(30)
                        ->required()
                        ->helperText('Détermine le pas des créneaux proposés.'),

                    TextInput::make('buffer_minutes')
                        ->label('Tampon après le RDV (minutes)')
                        ->numeric()
                        ->minValue(0)
                        ->step(5)
                        ->default(0)
                        ->required()
                        ->helperText('Temps de battement ajouté après chaque rendez-vous.'),

                    TextInput::make('price')
                        ->label('Prix')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->suffix('€')
                        ->default(0)
                        ->required()
                        ->helperText('0 € = prestation gratuite (ex. RDV découverte).'),

                    ColorPicker::make('color')
                        ->label('Couleur de repère')
                        ->helperText('Couleur utilisée pour distinguer cette prestation dans votre agenda.'),
                ]),

            Section::make('Règles de réservation')
                ->columns(2)
                ->schema([
                    TextInput::make('min_notice_hours')
                        ->label('Délai minimum avant RDV (heures)')
                        ->numeric()
                        ->minValue(0)
                        ->default(12)
                        ->required()
                        ->helperText('Empêche les réservations trop proches (ex. 12 h = pas de RDV pour le lendemain matin pris dans la nuit).'),

                    TextInput::make('max_advance_days')
                        ->label('Horizon de réservation (jours)')
                        ->numeric()
                        ->minValue(1)
                        ->default(60)
                        ->required()
                        ->helperText('Jusqu\'à combien de jours à l\'avance on peut réserver.'),

                    Toggle::make('requires_confirmation')
                        ->label('Validation manuelle requise')
                        ->default(false)
                        ->onColor('warning')
                        ->helperText('Activé = la réservation reste « en attente » jusqu\'à votre confirmation.')
                        ->inline(false),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->onColor('success')
                        ->helperText('Désactivée = prestation cachée du site public.')
                        ->inline(false),

                    TextInput::make('sort_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),
        ]);
    }
}
