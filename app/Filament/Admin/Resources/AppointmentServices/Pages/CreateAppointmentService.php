<?php

namespace App\Filament\Admin\Resources\AppointmentServices\Pages;

use App\Filament\Admin\Resources\AppointmentServices\AppointmentServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointmentService extends CreateRecord
{
    protected static string $resource = AppointmentServiceResource::class;
}
