<?php

namespace App\Filament\Admin\Resources\Availabilities;

use App\Filament\Admin\Resources\Availabilities\Pages\CreateAvailability;
use App\Filament\Admin\Resources\Availabilities\Pages\EditAvailability;
use App\Filament\Admin\Resources\Availabilities\Pages\ListAvailabilities;
use App\Filament\Admin\Resources\Availabilities\Schemas\AvailabilityForm;
use App\Filament\Admin\Resources\Availabilities\Tables\AvailabilitiesTable;
use App\Models\Availability;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Disponibilités';

    protected static ?string $modelLabel = 'Disponibilité';

    protected static ?string $pluralModelLabel = 'Disponibilités';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 20;

    /**
     * Libellés des jours de la semaine, indexés par le dayOfWeek de Carbon (0 = dimanche).
     *
     * @return array<int, string>
     */
    public static function weekdays(): array
    {
        return [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            0 => 'Dimanche',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AvailabilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvailabilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilities::route('/'),
            'create' => CreateAvailability::route('/create'),
            'edit' => EditAvailability::route('/{record}/edit'),
        ];
    }
}
