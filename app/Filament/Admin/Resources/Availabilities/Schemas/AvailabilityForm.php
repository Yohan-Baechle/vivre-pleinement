<?php

namespace App\Filament\Admin\Resources\Availabilities\Schemas;

use App\Filament\Admin\Resources\Availabilities\AvailabilityResource;
use App\Models\AppointmentService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AvailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('appointment_service_id')
                    ->label('Prestation')
                    ->options(fn () => AppointmentService::query()->orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Toutes les prestations')
                    ->helperText('Laissez vide pour appliquer ce créneau à toutes les prestations.')
                    ->native(false)
                    ->columnSpanFull(),

                Select::make('day_of_week')
                    ->label('Jour')
                    ->options(AvailabilityResource::weekdays())
                    ->required()
                    ->native(false),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->onColor('success')
                    ->inline(false),

                TimePicker::make('start_time')
                    ->label('Heure de début')
                    ->seconds(false)
                    ->required(),

                TimePicker::make('end_time')
                    ->label('Heure de fin')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
            ])
            ->columns(2);
    }
}
