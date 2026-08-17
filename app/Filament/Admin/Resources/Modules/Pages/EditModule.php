<?php

namespace App\Filament\Admin\Resources\Modules\Pages;

use App\Filament\Admin\Resources\Courses\CourseResource;
use App\Filament\Admin\Resources\Modules\ModuleResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    /**
     * Le ModuleResource n'a pas de page « index » : on reconstruit le fil
     * d'Ariane manuellement vers la formation parente.
     *
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            CourseResource::getUrl('index') => 'Formations',
            CourseResource::getUrl('edit', ['record' => $this->record->course_id]) => $this->record->course->title,
            $this->record->title,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_course')
                ->label('Retour à la formation')
                ->url(fn (): string => CourseResource::getUrl('edit', ['record' => $this->record->course_id]))
                ->color('gray'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CourseResource::getUrl('edit', ['record' => $this->record->course_id]);
    }
}
