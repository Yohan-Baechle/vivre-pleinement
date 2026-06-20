<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
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
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Select::make('parent_id')
                        ->label('Catégorie parente')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload(),

                    Select::make('pillar_post_id')
                        ->label('Article pilier')
                        ->helperText('Article de référence du cluster, mis en avant par le bloc « Pour aller plus loin ».')
                        ->relationship(
                            name: 'pillarPost',
                            titleAttribute: 'title',
                            modifyQueryUsing: fn (Builder $query, ?Category $record) => $record
                                ? $query->whereHas('categories', fn (Builder $q) => $q->whereKey($record->getKey()))
                                : $query,
                        )
                        ->searchable()
                        ->preload(),

                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('SEO')
                ->columns(2)
                ->collapsed()
                ->collapsible()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Titre SEO')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('seo_description')
                        ->label('Description SEO')
                        ->maxLength(320)
                        ->rows(3)
                        ->helperText('320 caractères max.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
