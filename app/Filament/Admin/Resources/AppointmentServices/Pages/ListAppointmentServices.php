<?php

namespace App\Filament\Admin\Resources\AppointmentServices\Pages;

use App\Filament\Admin\Resources\AppointmentServices\AppointmentServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAppointmentServices extends ListRecords
{
    protected static string $resource = AppointmentServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
