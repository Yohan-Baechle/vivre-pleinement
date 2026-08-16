<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Point d'accès unique aux deux produits vendus depuis la page du livre.
 *
 * L'accueil et la page du livre affichent tous deux un prix : les faire lire
 * la même source évite qu'une modification en admin ne se reflète que d'un
 * côté, avec une page qui annonce un montant et Stripe qui en débite un autre.
 */
class BookOffers
{
    public const SOLO = 'livre';

    public const COACHING = 'livre-coaching';

    /**
     * @var list<string>
     */
    public const SLUGS = [self::SOLO, self::COACHING];

    /**
     * Offres actives, indexées par slug.
     *
     * @return Collection<string, Product>
     */
    public function active(): Collection
    {
        return Product::query()
            ->whereIn('slug', self::SLUGS)
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');
    }

    public function find(string $slug): ?Product
    {
        if (! in_array($slug, self::SLUGS, true)) {
            return null;
        }

        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Prix affichable d'une offre, ou le repli fourni quand le produit
     * n'existe pas encore en base.
     */
    public function price(string $slug, float $fallback): float
    {
        return $this->find($slug)?->price ?? $fallback;
    }
}
