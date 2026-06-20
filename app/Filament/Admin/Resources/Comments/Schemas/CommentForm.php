<?php

namespace App\Filament\Admin\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Commentaire')
                ->columns(2)
                ->schema([
                    Select::make('post_id')
                        ->label('Article')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('author_name')
                        ->label('Auteur')
                        ->required(),

                    TextInput::make('author_email')
                        ->label('Email')
                        ->email(),

                    Textarea::make('content')
                        ->label('Contenu')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),

                    Select::make('status')
                        ->label('Statut')
                        ->options(CommentStatus::class)
                        ->default(CommentStatus::Approved)
                        ->required(),

                    DateTimePicker::make('posted_at')
                        ->label('Posté le')
                        ->seconds(false),
                ]),

            Section::make('Réponse à')
                ->collapsed()
                ->collapsible()
                ->schema([
                    Select::make('parent_id')
                        ->label('Commentaire parent')
                        ->relationship('parent', 'id')
                        ->searchable(),
                ]),

            Section::make('Détails techniques')
                ->columns(2)
                ->collapsed()
                ->collapsible()
                ->schema([
                    TextInput::make('author_url')
                        ->label('URL auteur')
                        ->url(),

                    TextInput::make('author_ip')
                        ->label('IP')
                        ->disabled(),
                ]),
        ]);
    }
}
