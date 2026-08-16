<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

function fakeBlogImage(string $name, int $width = 2000): void
{
    $image = imagecreatetruecolor($width, (int) ($width / 1.5));
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 120, 180, 170));

    $path = Storage::disk('public')->path($name);

    match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
        'png' => imagepng($image, $path),
        default => imagejpeg($image, $path),
    };

    imagedestroy($image);
}

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->makeDirectory('blog-images');
});

it('converts legacy images to capped-width webp and rewrites the content', function () {
    fakeBlogImage('blog-images/exemple.jpg');
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<img fetchpriority="high" decoding="async" width="2880" height="1920" src="/storage/blog-images/exemple.jpg" alt="Exemple">',
    ]);

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect(Storage::disk('public')->exists('blog-images/exemple.webp'))->toBeTrue();

    [$width, $height] = getimagesize(Storage::disk('public')->path('blog-images/exemple.webp'));
    expect($width)->toBe(1200);

    $content = $post->refresh()->content;
    expect($content)->toContain('src="/storage/blog-images/exemple.webp"')
        ->and($content)->toContain('loading="lazy"')
        ->and($content)->not->toContain('fetchpriority')
        ->and($content)->toContain('decoding="async"')
        ->and($content)->toContain('width="1200"')
        ->and($content)->toContain('height="'.$height.'"')
        ->and($content)->not->toContain('width="2880"');
});

it('generates srcset variants and wires them on the img tag', function () {
    fakeBlogImage('blog-images/exemple.jpg');
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<img width="2880" height="1920" src="/storage/blog-images/exemple.jpg" alt="">',
    ]);

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect(Storage::disk('public')->exists('blog-images/exemple-400w.webp'))->toBeTrue()
        ->and(Storage::disk('public')->exists('blog-images/exemple-800w.webp'))->toBeTrue();

    $content = $post->refresh()->content;
    expect($content)->toContain('srcset="/storage/blog-images/exemple-400w.webp 400w, /storage/blog-images/exemple-800w.webp 800w, /storage/blog-images/exemple.webp 1200w"')
        ->and($content)->toContain('sizes="(min-width: 640px) 600px, 100vw"');

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect(substr_count($post->refresh()->content, 'srcset='))->toBe(1);
});

it('skips srcset for images smaller than the variant widths', function () {
    fakeBlogImage('blog-images/petite.jpg', 350);
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<img width="350" height="233" src="/storage/blog-images/petite.jpg" alt="">',
    ]);

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect(Storage::disk('public')->exists('blog-images/petite-400w.webp'))->toBeFalse()
        ->and($post->refresh()->content)->not->toContain('srcset=');
});

it('keeps the original file on disk for legacy inbound traffic', function () {
    fakeBlogImage('blog-images/exemple.jpg');
    Post::factory()->create([
        'status' => 'published',
        'content' => '<img src="/storage/blog-images/exemple.jpg" alt="">',
    ]);

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect(Storage::disk('public')->exists('blog-images/exemple.jpg'))->toBeTrue();
});

it('leaves the src untouched when no webp variant exists', function () {
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<img fetchpriority="high" src="/storage/blog-images/absente.jpg" alt="">',
    ]);

    $this->artisan('posts:convert-content-images')->assertSuccessful();

    expect($post->refresh()->content)->toContain('src="/storage/blog-images/absente.jpg"')
        ->and($post->refresh()->content)->toContain('loading="lazy"');
});

it('changes nothing in dry-run mode', function () {
    fakeBlogImage('blog-images/exemple.jpg');
    $original = '<img fetchpriority="high" src="/storage/blog-images/exemple.jpg" alt="">';
    $post = Post::factory()->create(['status' => 'published', 'content' => $original]);

    $this->artisan('posts:convert-content-images --dry-run')->assertSuccessful();

    expect(Storage::disk('public')->exists('blog-images/exemple.webp'))->toBeFalse()
        ->and($post->refresh()->content)->toBe($original);
});
