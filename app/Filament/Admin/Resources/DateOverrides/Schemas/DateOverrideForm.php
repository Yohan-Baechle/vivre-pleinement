<?php

namespace App\Filament\Admin\Resources\DateOverrides\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class DateOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),

                TimePicker::make('start_time')
                    ->label('Heure de début')
                    ->seconds(false)
                    ->helperText('Laissez vide pour bloquer toute la journée.'),

                TimePicker::make('end_time')
                    ->label('Heure de fin')
                    ->seconds(false)
                    ->after('start_time')
                    ->helperText('Laissez vide pour bloquer toute la journée.'),

                TextInput::make('reason')
                    ->label('Motif (optionnel)')
                    ->placeholder('Congés, formation, indisponible…')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
