<?php

namespace App\Filament\Admin\Resources\DateOverrides;

use App\Filament\Admin\Resources\DateOverrides\Pages\CreateDateOverride;
use App\Filament\Admin\Resources\DateOverrides\Pages\EditDateOverride;
use App\Filament\Admin\Resources\DateOverrides\Pages\ListDateOverrides;
use App\Filament\Admin\Resources\DateOverrides\Schemas\DateOverrideForm;
use App\Filament\Admin\Resources\DateOverrides\Tables\DateOverridesTable;
use App\Models\DateOverride;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DateOverrideResource extends Resource
{
    protected static ?string $model = DateOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Congés & blocages';

    protected static ?string $modelLabel = 'congé / blocage';

    protected static ?string $pluralModelLabel = 'Congés & blocages';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return DateOverrideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DateOverridesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDateOverrides::route('/'),
            'create' => CreateDateOverride::route('/create'),
            'edit' => EditDateOverride::route('/{record}/edit'),
        ];
    }
}
