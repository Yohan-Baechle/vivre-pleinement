<?php

namespace App\Filament\Admin\Resources\AppointmentServices;

use App\Filament\Admin\Resources\AppointmentServices\Pages\CreateAppointmentService;
use App\Filament\Admin\Resources\AppointmentServices\Pages\EditAppointmentService;
use App\Filament\Admin\Resources\AppointmentServices\Pages\ListAppointmentServices;
use App\Filament\Admin\Resources\AppointmentServices\Schemas\AppointmentServiceForm;
use App\Filament\Admin\Resources\AppointmentServices\Tables\AppointmentServicesTable;
use App\Models\AppointmentService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AppointmentServiceResource extends Resource
{
    protected static ?string $model = AppointmentService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Prestations';

    protected static ?string $modelLabel = 'Prestation';

    protected static ?string $pluralModelLabel = 'Prestations';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function form(Schema $schema): Schema
    {
        return AppointmentServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointmentServices::route('/'),
            'create' => CreateAppointmentService::route('/create'),
            'edit' => EditAppointmentService::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
