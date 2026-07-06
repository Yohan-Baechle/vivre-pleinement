<?php

namespace App\Filament\Admin\Resources\Enrollments;

use App\Filament\Admin\Resources\Enrollments\Pages\ListEnrollments;
use App\Filament\Admin\Resources\Enrollments\Tables\EnrollmentsTable;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Ventes';

    protected static ?string $modelLabel = 'Vente';

    protected static ?string $pluralModelLabel = 'Ventes';

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Formations';

    public static function table(Table $table): Table
    {
        return EnrollmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnrollments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
