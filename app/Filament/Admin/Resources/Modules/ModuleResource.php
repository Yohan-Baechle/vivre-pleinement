<?php

namespace App\Filament\Admin\Resources\Modules;

use App\Filament\Admin\Resources\Modules\Pages\EditModule;
use App\Filament\Admin\Resources\Modules\RelationManagers\LessonsRelationManager;
use App\Models\Module;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $modelLabel = 'Module';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Géré depuis la formation parente : pas d'entrée de navigation propre.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Titre du module')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditModule::route('/{record}/edit'),
        ];
    }
}
