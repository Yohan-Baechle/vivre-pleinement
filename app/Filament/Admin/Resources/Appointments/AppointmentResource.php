<?php

namespace App\Filament\Admin\Resources\Appointments;

use App\Enums\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Admin\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Admin\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Admin\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Admin\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Rendez-vous';

    protected static ?string $modelLabel = 'rendez-vous';

    protected static ?string $pluralModelLabel = 'Rendez-vous';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'customer_first_name', 'customer_last_name', 'customer_email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', AppointmentStatus::Pending)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }
}
