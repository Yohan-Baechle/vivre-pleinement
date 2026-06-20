<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_service_id',
    'day_of_week',
    'start_time',
    'end_time',
    'is_active',
])]
class Availability extends Model
{
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AppointmentService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(AppointmentService::class, 'appointment_service_id');
    }
}
