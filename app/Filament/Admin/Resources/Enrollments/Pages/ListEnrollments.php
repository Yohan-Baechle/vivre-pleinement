<?php

namespace App\Filament\Admin\Resources\Enrollments\Pages;

use App\Filament\Admin\Resources\Enrollments\EnrollmentResource;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;
}
