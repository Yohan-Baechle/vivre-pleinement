<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPriceInCents
{
    /**
     * Expose la colonne entière `price_cents` comme un `price` décimal en unité principale.
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_cents / 100,
            set: fn (float|int $value) => ['price_cents' => (int) round($value * 100)],
        );
    }
}
