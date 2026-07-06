<?php

namespace App\Support;

/**
 * Entité schema.org de Laura, partagée entre la home, les articles et les vidéos
 * pour que les moteurs fusionnent les signaux auteur sur un @id unique.
 */
class AuthorEntity
{
    /**
     * Nœud Person complet, identifié par l'@id canonique `#laura`.
     *
     * @return array<string, mixed>
     */
    public static function person(): array
    {
        $sameAs = array_values(SiteContact::socials());

        return array_filter([
            '@type' => 'Person',
            '@id' => url('/').'#laura',
            'name' => 'Laura Baechlé',
            'jobTitle' => 'Praticienne ACT en accompagnement des troubles anxieux',
            'url' => route('about'),
            'image' => asset('images/laura-portrait-1200.webp'),
            'description' => "Praticienne ACT spécialisée dans l'accompagnement des personnes souffrant de troubles anxieux : anxiété généralisée (TAG), phobies, TOC, burnout.",
            'knowsAbout' => ['Troubles anxieux', 'Anxiété généralisée', 'TAG', 'Phobies', 'TOC', 'Burnout', 'Thérapie ACT', 'Gestion du stress', 'Bien-être mental'],
            'sameAs' => $sameAs ?: null,
        ]);
    }
}
