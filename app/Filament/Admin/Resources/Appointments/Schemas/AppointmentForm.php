<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Models\AppointmentService;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rendez-vous')
                ->columns(2)
                ->schema([
                    Select::make('appointment_service_id')
                        ->label('Prestation')
                        ->options(fn () => AppointmentService::query()->orderBy('name')->pluck('name', 'id'))
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            self::computeEnd($state, $get('starts_at'), $set);
                        })
                        ->columnSpanFull(),

                    DateTimePicker::make('starts_at')
                        ->label('Début')
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            self::computeEnd($get('appointment_service_id'), $state, $set);
                        }),

                    DateTimePicker::make('ends_at')
                        ->label('Fin')
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->required()
                        ->after('starts_at')
                        ->helperText('Calculée automatiquement selon la prestation. Ajustez si besoin.'),

                    Select::make('status')
                        ->label('Statut')
                        ->options(AppointmentStatus::class)
                        ->default(AppointmentStatus::Confirmed)
                        ->required()
                        ->native(false),

                    Select::make('channel')
                        ->label('Format du rendez-vous')
                        ->options(AppointmentChannel::class)
                        ->default(AppointmentChannel::Video)
                        ->required()
                        ->native(false),

                    TextInput::make('meeting_url')
                        ->label('Lien visio personnalisé')
                        ->url()
                        ->placeholder('Laisser vide pour utiliser le lien par défaut')
                        ->helperText('Remplace le lien Google Meet habituel, uniquement pour ce rendez-vous.')
                        ->columnSpanFull(),
                ]),

            Section::make('Client')
                ->columns(2)
                ->schema([
                    TextInput::make('customer_first_name')
                        ->label('Prénom')
                        ->required(),

                    TextInput::make('customer_last_name')
                        ->label('Nom'),

                    TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->required(),

                    TextInput::make('customer_phone')
                        ->label('Téléphone')
                        ->tel(),

                    Textarea::make('notes')
                        ->label('Message / notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Renseigne l'heure de fin à partir de la durée (et du tampon) de la prestation choisie.
     */
    protected static function computeEnd(mixed $serviceId, ?string $startsAt, Set $set): void
    {
        if (! $serviceId || ! $startsAt) {
            return;
        }

        $service = AppointmentService::find($serviceId);

        if (! $service) {
            return;
        }

        $set('ends_at', Carbon::parse($startsAt)->addMinutes($service->duration_minutes)->format('Y-m-d H:i:s'));
    }
}
