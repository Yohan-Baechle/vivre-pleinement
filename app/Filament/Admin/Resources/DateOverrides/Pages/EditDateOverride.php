<?php

namespace App\Filament\Admin\Resources\DateOverrides\Pages;

use App\Filament\Admin\Resources\DateOverrides\DateOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDateOverride extends EditRecord
{
    protected static string $resource = DateOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
