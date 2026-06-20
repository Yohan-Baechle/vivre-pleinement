<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $state, callable $set, $record) {
                    if (! $record) {
                        $set('slug', Str::slug($state));
                    }
                }),

            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }
}
