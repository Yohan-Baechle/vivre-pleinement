<?php

namespace App\Filament\Admin\Resources\BookOrders\Pages;

use App\Filament\Admin\Resources\BookOrders\BookOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListBookOrders extends ListRecords
{
    protected static string $resource = BookOrderResource::class;
}
