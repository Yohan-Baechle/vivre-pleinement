<?php

namespace App\Filament\Admin\Resources\BookOrders;

use App\Filament\Admin\Resources\BookOrders\Pages\ListBookOrders;
use App\Filament\Admin\Resources\BookOrders\Tables\BookOrdersTable;
use App\Models\BookOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookOrderResource extends Resource
{
    protected static ?string $model = BookOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Commandes du livre';

    protected static ?string $modelLabel = 'Commande du livre';

    protected static ?string $pluralModelLabel = 'Commandes du livre';

    protected static ?int $navigationSort = 45;

    protected static string|UnitEnum|null $navigationGroup = 'Boutique';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function table(Table $table): Table
    {
        return BookOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
