<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Avatar d'initiales généré sur place, en SVG encodé en data:.
 *
 * Le fournisseur par défaut de Filament appelle ui-avatars.com : la politique
 * de sécurité du site n'autorise pas cette origine dans img-src, l'image
 * échouait donc et le navigateur affichait son texte de remplacement, rogné
 * en « AVAT » par le rond de l'avatar. Générer le SVG localement évite à la
 * fois l'appel bloqué et l'envoi du nom de l'utilisateur à un tiers.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    private const BACKGROUND = '#117d89';

    public function get(Model|Authenticatable $record): string
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
            <rect width="100" height="100" fill="{$this->background()}"/>
            <text x="50" y="50" fill="#ffffff" font-family="system-ui, sans-serif"
                  font-size="42" font-weight="600" text-anchor="middle"
                  dominant-baseline="central">{$this->initials($record)}</text>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function background(): string
    {
        return self::BACKGROUND;
    }

    /**
     * Une lettre par mot du nom, deux au maximum.
     */
    private function initials(Model|Authenticatable $record): string
    {
        $name = trim(Filament::getNameForDefaultAvatar($record));

        $initials = collect(preg_split('/\s+/', $name) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return htmlspecialchars($initials ?: '?', ENT_QUOTES | ENT_XML1);
    }
}
