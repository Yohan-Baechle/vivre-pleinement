<?php

namespace App\Support;

/**
 * Coordonnées publiques du site, éditables depuis l'admin (table settings).
 */
class SiteContact
{
    public static function email(): string
    {
        $value = Settings::get('contact_email');

        return filled($value) ? $value : config('mail.contact_to');
    }

    /**
     * Adresse de réception des notifications (modération, contact).
     */
    public static function notifyEmail(): string
    {
        $value = Settings::get('notify_email');

        return filled($value) ? $value : config('mail.contact_to');
    }

    public static function phone(): ?string
    {
        $value = Settings::get('contact_phone');

        return filled($value) ? $value : null;
    }

    /**
     * Numéro nettoyé pour un lien tel: (chiffres et + uniquement).
     */
    public static function phoneHref(): ?string
    {
        $phone = self::phone();

        return $phone === null ? null : preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Liens réseaux sociaux renseignés, indexés par nom.
     *
     * @return array<string, string>
     */
    public static function socials(): array
    {
        $links = [
            'Instagram' => Settings::get('social_instagram'),
            'Facebook' => Settings::get('social_facebook'),
            'YouTube' => Settings::get('social_youtube'),
            'TikTok' => Settings::get('social_tiktok'),
        ];

        return array_filter($links, fn ($url) => filled($url));
    }
}
