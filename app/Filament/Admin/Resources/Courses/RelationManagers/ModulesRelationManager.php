<?php

namespace App\Filament\Admin\Resources\Courses\RelationManagers;

use App\Filament\Admin\Resources\Modules\ModuleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Modules';

    protected static ?string $modelLabel = 'module';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Titre du module')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('title')
                    ->label('Module')
                    ->weight('medium'),
                TextColumn::make('lessons_count')
                    ->label('Leçons')
                    ->counts('lessons'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un module'),
            ])
            ->recordActions([
                Action::make('lessons')
                    ->label('Gérer les leçons')
                    ->icon(Heroicon::OutlinedListBullet)
                    ->url(fn ($record): string => ModuleResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
