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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'EUR',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * La collection download (fichier vendu) vit sur le disque privé : sur le
     * disque public, l'URL /storage/{media_id}/… serait devinable et le
     * fichier téléchargeable sans achat.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('download')->singleFile()->useDisk('local');
    }

    /**
     * Slug du produit qui fait référence pour le fichier du livre. Les
     * formules qui l'embarquent héritent de son fichier plutôt que d'en
     * exiger une copie : deux téléversements à maintenir finiraient par
     * diverger, et un acheteur recevrait une version périmée.
     */
    private const BOOK_FILE_SOURCE_SLUG = 'livre';

    /**
     * Fichier réellement livré à l'acheteur : celui du produit s'il en a un,
     * sinon celui du livre seul.
     */
    public function deliverableMedia(): ?Media
    {
        $own = $this->getFirstMedia('download');

        if ($own !== null) {
            return $own;
        }

        if ($this->slug === self::BOOK_FILE_SOURCE_SLUG) {
            return null;
        }

        return static::query()
            ->where('slug', self::BOOK_FILE_SOURCE_SLUG)
            ->first()
            ?->getFirstMedia('download');
    }

    /**
     * Une offre sans fichier livrable ne doit pas pouvoir être achetée : le
     * client paierait pour un contenu qui n'existe pas.
     */
    public function isDeliverable(): bool
    {
        return $this->deliverableMedia() !== null;
    }

    /**
     * Le fichier vient d'un autre produit : à signaler en admin pour que
     * l'héritage ne soit pas une surprise.
     */
    public function usesInheritedFile(): bool
    {
        return $this->getFirstMedia('download') === null && $this->isDeliverable();
    }
}
