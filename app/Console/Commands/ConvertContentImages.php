<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

#[Signature('posts:convert-content-images {--dry-run : Affiche les changements sans convertir ni enregistrer}')]
#[Description('Convertit les images legacy du contenu des articles en WebP (max 1200 px), aligne les attributs width/height et remplace fetchpriority="high" par loading="lazy".')]
class ConvertContentImages extends Command
{
    private const MAX_WIDTH = 1200;

    private const WEBP_QUALITY = 80;

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $converted = $this->convertFiles($dry);
        $rewritten = $this->rewriteContent($dry);

        $this->newLine();
        $this->comment(($dry ? '[dry] ' : '')."{$converted} image(s) convertie(s) en WebP, {$rewritten} article(s) réécrit(s).");

        return self::SUCCESS;
    }

    private function convertFiles(bool $dry): int
    {
        $disk = Storage::disk('public');
        $converted = 0;

        foreach ($disk->files('blog-images') as $file) {
            if (! preg_match('/\.(jpe?g|png)$/i', $file)) {
                continue;
            }

            $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);

            if ($disk->exists($webp)) {
                continue;
            }

            $converted++;

            if ($dry) {
                $this->line("[dry] {$file} → {$webp}");

                continue;
            }

            if (! $this->createWebp($disk->path($file), $disk->path($webp))) {
                $this->warn("Conversion impossible : {$file}");
                $converted--;

                continue;
            }

            $this->line("✓ {$file} → ".$this->formatKb($disk->size($file)).' → '.$this->formatKb($disk->size($webp)));
        }

        return $converted;
    }

    private function rewriteContent(bool $dry): int
    {
        $disk = Storage::disk('public');
        $rewritten = 0;

        foreach (Post::query()->where('content', 'like', '%/storage/blog-images/%')->cursor() as $post) {
            $content = preg_replace_callback(
                '/<img[^>]*\/storage\/blog-images\/[^>]*>/i',
                function (array $match) use ($disk): string {
                    $tag = preg_replace_callback(
                        '/(src="\/storage\/)(blog-images\/[^"]+)\.(jpe?g|png)(")/i',
                        function (array $src) use ($disk): string {
                            $webp = $src[2].'.webp';

                            return $disk->exists($webp) ? $src[1].$webp.$src[4] : $src[0];
                        },
                        $match[0],
                    );

                    $tag = preg_replace('/\s*fetchpriority="high"/i', '', $tag);

                    if (! str_contains($tag, 'loading=')) {
                        $tag = preg_replace('/^<img/i', '<img loading="lazy"', $tag);
                    }

                    return $this->syncDimensionAttributes($tag, $disk);
                },
                $post->content,
            );

            if ($content === $post->content) {
                continue;
            }

            $rewritten++;

            if ($dry) {
                $this->line("[dry] à réécrire : {$post->slug}");

                continue;
            }

            $post->content = $content;
            $post->save();
        }

        return $rewritten;
    }

    /**
     * Aligne les attributs width/height hérités de WordPress (ex. 2880×1920)
     * sur les dimensions réelles du fichier WebP servi.
     */
    private function syncDimensionAttributes(string $tag, Filesystem $disk): string
    {
        if (! preg_match('/src="\/storage\/(blog-images\/[^"]+\.webp)"/i', $tag, $match) || ! $disk->exists($match[1])) {
            return $tag;
        }

        $size = @getimagesize($disk->path($match[1]));

        if ($size === false) {
            return $tag;
        }

        $tag = preg_replace('/width="\d+"/i', 'width="'.$size[0].'"', $tag);

        return preg_replace('/height="\d+"/i', 'height="'.$size[1].'"', $tag);
    }

    private function createWebp(string $source, string $destination): bool
    {
        $image = match (@mime_content_type($source) ?: '') {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            default => false,
        };

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);

        if ($width > self::MAX_WIDTH) {
            $resized = imagescale($image, self::MAX_WIDTH);
            imagedestroy($image);

            if ($resized === false) {
                return false;
            }

            $image = $resized;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $saved = imagewebp($image, $destination, self::WEBP_QUALITY);
        imagedestroy($image);

        return $saved;
    }

    private function formatKb(int $bytes): string
    {
        return round($bytes / 1024).' Ko';
    }
}
