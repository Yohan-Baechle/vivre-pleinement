<?php

namespace App\Models;

use App\Models\Concerns\HasOptimizedMedia;
use App\Models\Concerns\HasPriceInCents;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'name',
    'slug',
    'short_description',
    'description',
    'price',
    'price_cents',
    'currency',
    'stripe_payment_link',
    'is_active',
    'seo_title',
    'seo_description',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasOptimizedMedia, InteractsWithMedia {
        HasOptimizedMedia::registerMediaConversions insteadof InteractsWithMedia;
    }
    use HasPriceInCents;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('download')->singleFile();
    }
}
