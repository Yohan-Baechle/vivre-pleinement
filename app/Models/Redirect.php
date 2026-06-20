<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'from_path',
    'to_path',
    'status_code',
    'hit_count',
    'last_hit_at',
])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_hit_at' => 'datetime',
            'status_code' => 'integer',
            'hit_count' => 'integer',
        ];
    }
}
