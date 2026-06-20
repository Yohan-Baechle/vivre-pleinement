<?php

namespace App\Filament\Admin\Resources\DateOverrides\Pages;

use App\Filament\Admin\Resources\DateOverrides\DateOverrideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDateOverride extends CreateRecord
{
    protected static string $resource = DateOverrideResource::class;
}
