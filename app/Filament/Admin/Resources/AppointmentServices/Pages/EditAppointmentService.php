<?php

namespace App\Filament\Admin\Resources\AppointmentServices\Pages;

use App\Filament\Admin\Resources\AppointmentServices\AppointmentServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAppointmentService extends EditRecord
{
    protected static string $resource = AppointmentServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Prestation enregistrée');
    }
}
