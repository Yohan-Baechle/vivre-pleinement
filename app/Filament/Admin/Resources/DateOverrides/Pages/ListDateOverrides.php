<?php

namespace App\Filament\Admin\Resources\DateOverrides\Pages;

use App\Filament\Admin\Resources\DateOverrides\DateOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDateOverrides extends ListRecords
{
    protected static string $resource = DateOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
