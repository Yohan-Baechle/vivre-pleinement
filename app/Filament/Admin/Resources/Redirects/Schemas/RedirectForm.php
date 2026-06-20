<?php

namespace App\Filament\Admin\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')
                ->label('URL source')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('/ancien-article')
                ->helperText('Doit commencer par /')
                ->prefix(url('/'))
                ->columnSpanFull(),

            TextInput::make('to_path')
                ->label('URL cible')
                ->required()
                ->placeholder('/nouveau-article')
                ->prefix(url('/'))
                ->columnSpanFull(),

            Select::make('status_code')
                ->label('Code HTTP')
                ->options([
                    301 => '301 – Permanent',
                    302 => '302 – Temporaire',
                ])
                ->default(301)
                ->required(),
        ]);
    }
}
