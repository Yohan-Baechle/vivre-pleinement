<?php

namespace App\Filament\Admin\Resources\Comments\Schemas;

use App\Models\Comment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(3)
                ->schema([
                    TextEntry::make('author_name')
                        ->label('Auteur'),

                    TextEntry::make('author_email')
                        ->label('Email')
                        ->placeholder('–')
                        ->copyable(),

                    TextEntry::make('posted_at')
                        ->label('Posté le')
                        ->dateTime('d/m/Y à H:i'),

                    TextEntry::make('post.title')
                        ->label('Sous l\'article')
                        ->placeholder('Article supprimé')
                        ->url(fn (Comment $record) => $record->post
                            ? route('filament.admin.resources.posts.edit', $record->post)
                            : null)
                        ->columnSpan(2),

                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge(),

                    TextEntry::make('content')
                        ->label('Commentaire')
                        ->prose()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
