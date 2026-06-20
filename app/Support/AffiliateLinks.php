<?php

namespace App\Support;

/**
 * Met en valeur les liens affiliés du corps d'un article en les transformant,
 * au rendu, en encarts « carte produit » typés et plus cliquables.
 *
 * Le contenu stocké reste un simple lien — la transformation est purement
 * cosmétique et centralisée, donc les nouveaux articles rédigés dans l'admin
 * en bénéficient automatiquement sans HTML particulier.
 */
class AffiliateLinks
{
    /**
     * Configuration par type d'affiliation, détecté sur le domaine du lien.
     * L'ordre compte : la première entrée dont un `host` matche est retenue.
     *
     * @var array<string, array{hosts: array<string>, eyebrow: string, cta: string, icon: string, fallback: string}>
     */
    private const TYPES = [
        'book' => [
            'hosts' => ['amzn.to', 'amazon.fr', 'amazon.com'],
            'eyebrow' => 'Le livre recommandé',
            'cta' => 'Voir sur Amazon',
            'icon' => 'book',
            'fallback' => 'Découvrir le livre',
        ],
        'audio' => [
            'hosts' => ['hypnocaments.com'],
            'eyebrow' => 'Séance d’hypnose en ligne',
            'cta' => 'Écouter un extrait',
            'icon' => 'headphones',
            'fallback' => 'Découvrir les séances d’hypnose',
        ],
        'plants' => [
            'hosts' => ['ruedesplantes.com'],
            'eyebrow' => 'Complément naturel',
            'cta' => 'Voir le produit',
            'icon' => 'leaf',
            'fallback' => 'Découvrir le complément',
        ],
        'generic' => [
            'hosts' => ['1tpe.net'],
            'eyebrow' => 'Ressource recommandée',
            'cta' => 'Découvrir',
            'icon' => 'sparkles',
            'fallback' => 'Découvrir la ressource recommandée',
        ],
    ];

    /**
     * Transforme en un seul passage chaque `<a>…</a>` affilié en carte produit,
     * qu'il enveloppe une image-bannière ou du texte. Le passage unique évite de
     * re-traiter le bouton interne des cartes déjà générées.
     */
    public static function enhance(string $html): string
    {
        return preg_replace_callback(
            '#<a\b([^>]*)>(.*?)</a>#is',
            function (array $m): string {
                $href = self::extractHref($m[1]);
                $type = self::resolveType($href);

                if ($type === null) {
                    return $m[0];
                }

                $config = self::TYPES[$type];
                $label = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                return view('components.affiliate-card', [
                    'href' => $href,
                    'label' => $label !== '' ? $label : $config['fallback'],
                    'eyebrow' => $config['eyebrow'],
                    'cta' => $config['cta'],
                    'icon' => $config['icon'],
                ])->render();
            },
            $html,
        );
    }

    private static function extractHref(string $attributes): ?string
    {
        return preg_match('/\bhref="([^"]*)"/i', $attributes, $m) ? $m[1] : null;
    }

    /**
     * Renvoie la clé de type d'affiliation pour ce lien, ou null s'il n'est pas affilié.
     */
    private static function resolveType(?string $href): ?string
    {
        if (blank($href)) {
            return null;
        }

        $host = parse_url($href, PHP_URL_HOST) ?? '';

        foreach (self::TYPES as $type => $config) {
            foreach ($config['hosts'] as $needle) {
                if (str_contains($host, $needle)) {
                    return $type;
                }
            }
        }

        return null;
    }
}
