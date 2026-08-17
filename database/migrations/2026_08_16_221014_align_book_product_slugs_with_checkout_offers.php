<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Le tunnel d'achat du livre résout son produit par le slug présent dans
 * l'URL (/livre/commande/{slug}). Les deux produits créés à la main portaient
 * des slugs historiques : on les aligne sur les clés d'offre publiques.
 *
 * Aucune URL publique n'exposait ces slugs auparavant, la reprise est donc
 * sans effet SEO.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const RENAMES = [
        'soigner-le-toc-de-la-phobie-dimplusion' => 'livre',
        'ebook-coaching' => 'livre-coaching',
    ];

    public function up(): void
    {
        $this->rename(self::RENAMES);
    }

    public function down(): void
    {
        $this->rename(array_flip(self::RENAMES));
    }

    /**
     * @param  array<string, string>  $renames
     */
    private function rename(array $renames): void
    {
        foreach ($renames as $from => $to) {
            if (DB::table('products')->where('slug', $to)->exists()) {
                continue;
            }

            DB::table('products')->where('slug', $from)->update(['slug' => $to]);
        }
    }
};
