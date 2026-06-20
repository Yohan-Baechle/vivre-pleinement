<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'date',
    'start_time',
    'end_time',
    'reason',
])]
class DateOverride extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Indique si cette exception bloque la journée entière (aucune plage horaire définie).
     */
    public function isFullDay(): bool
    {
        return $this->start_time === null && $this->end_time === null;
    }
}
