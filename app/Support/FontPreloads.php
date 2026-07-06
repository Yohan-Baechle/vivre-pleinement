<?php

namespace App\Support;

/**
 * Filtre les preloads de polices au sous-ensemble latin uniquement.
 *
 * Bunny sert chaque variante en 2-3 fichiers découpés par unicode-range
 * (latin, latin-ext…) et la directive @fonts les précharge tous. Seul le
 * fichier latin (U+0000-00FF, qui couvre le français, œ compris) est
 * réellement utilisé au-dessus de la ligne de flottaison : précharger les
 * autres gaspille de la bande passante au détriment du LCP.
 */
class FontPreloads
{
    private const LATIN_RANGE_PREFIX = 'U+0000-00FF';

    /**
     * @var array<string, true>|null
     */
    private static ?array $latinFiles = null;

    public static function shouldPreload(string $url): bool
    {
        $latinFiles = self::latinFiles();

        if ($latinFiles === null) {
            return true;
        }

        return isset($latinFiles[basename(parse_url($url, PHP_URL_PATH) ?: $url)]);
    }

    public static function flush(): void
    {
        self::$latinFiles = null;
    }

    /**
     * Noms des fichiers woff2 couvrant le sous-ensemble latin, ou null si le
     * manifest de build est absent (dev server, CI sans build).
     *
     * @return array<string, true>|null
     */
    private static function latinFiles(): ?array
    {
        if (self::$latinFiles !== null) {
            return self::$latinFiles;
        }

        $path = public_path('build/fonts-manifest.json');

        if (! is_file($path)) {
            return null;
        }

        $manifest = json_decode((string) file_get_contents($path), true);

        if (! is_array($manifest)) {
            return null;
        }

        $files = [];

        foreach ($manifest['families'] ?? [] as $family) {
            foreach ($family['variants'] ?? [] as $variant) {
                foreach ($variant['files'] ?? [] as $file) {
                    if (str_starts_with($file['unicodeRange'] ?? '', self::LATIN_RANGE_PREFIX)) {
                        $files[basename($file['file'])] = true;
                    }
                }
            }
        }

        return self::$latinFiles = $files;
    }
}
