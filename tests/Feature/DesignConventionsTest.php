<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Finder\Finder;

uses(LazilyRefreshDatabase::class);

/**
 * Conventions de design tenues mécaniquement. Elles se redégradent seules
 * dès qu'on ajoute une section sans y penser : ces tests sont là pour que
 * la dérive échoue en CI plutôt que de se découvrir six mois plus tard.
 */

/**
 * @return array<string, string>
 */
function bladeViews(string $subPath = ''): array
{
    $files = [];

    foreach (Finder::create()->files()->in(resource_path('views/'.$subPath))->name('*.blade.php') as $file) {
        $files[$file->getRelativePathname()] = $file->getContents();
    }

    return $files;
}

it('ne contient aucun em dash dans les vues', function () {
    $offenders = array_keys(array_filter(bladeViews(), fn (string $c) => str_contains($c, '—')));

    expect($offenders)->toBe([]);
});

it('utilise dvh plutôt que vh pour les hauteurs plein écran', function () {
    $offenders = array_keys(array_filter(
        bladeViews(),
        fn (string $c) => preg_match('/\b(min-)?h-screen\b/', $c) === 1,
    ));

    expect($offenders)->toBe([]);
});

it('tient une échelle de rayons fermée', function () {
    $allowed = ['full', '5xl', '4xl', '3xl', '2xl', 'sm'];

    /**
     * Les surcharges d'un seul angle (rounded-br-md) échappent à l'échelle :
     * elles ne dimensionnent pas un bloc, elles dessinent la pointe d'une
     * bulle de dialogue dans l'accordéon.
     */
    $found = [];
    foreach (bladeViews() as $content) {
        preg_match_all('/\brounded-(?!t-|b-|l-|r-|tl-|tr-|bl-|br-)([a-z0-9]+)\b/', $content, $matches);
        $found = array_merge($found, $matches[1]);
    }

    expect(array_values(array_unique(array_diff($found, $allowed))))->toBe([]);
});

it('tient une échelle fermée d\'épaisseurs de trait SVG', function () {
    $allowed = ['1.8', '2.5', '4', '1'];

    $found = [];
    foreach (bladeViews() as $content) {
        preg_match_all('/stroke-width="([0-9.]+)"/', $content, $matches);
        $found = array_merge($found, $matches[1]);
    }

    expect(array_values(array_unique(array_diff($found, $allowed))))->toBe([]);
});

it('n\'emploie qu\'un seul libellé pour l\'intention « prendre rendez-vous »', function () {
    $offenders = array_keys(array_filter(bladeViews(), fn (string $c) => str_contains($c, 'Prendre RDV')));

    expect($offenders)->toBe([]);
});

it('ne pose pas de pastille décorative dans les pilules', function () {
    $offenders = array_keys(array_filter(
        bladeViews(),
        fn (string $c) => preg_match('/<span class="[^"]*size-1\.5 rounded-full[^"]*"><\/span>/', $c) === 1,
    ));

    expect($offenders)->toBe([]);
});

it('respecte le plafond d\'un eyebrow pour trois sections', function (string $page, int $sections) {
    $eyebrows = 0;

    foreach (bladeViews($page) as $content) {
        $eyebrows += substr_count($content, 'eyebrow="');
    }

    expect($eyebrows)->toBeLessThanOrEqual((int) ceil($sections / 3));
})->with([
    ['home', 8],
    ['book', 11],
    ['therapie-act', 6],
]);

it('affiche le prix du livre depuis le catalogue sur l\'accueil', function () {
    $product = Product::factory()->create([
        'slug' => 'livre',
        'name' => 'Le livre seul',
        'price_cents' => 4200,
    ]);

    $product->addMedia(UploadedFile::fake()->create('livre.pdf', 12))
        ->toMediaCollection('download');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Découvrir le livre · 42', false);
});

it('masque le prix du livre sur l\'accueil quand l\'offre n\'est pas livrable', function () {
    Product::factory()->create(['slug' => 'livre', 'price_cents' => 4200]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Découvrir le livre')
        ->assertDontSee('Découvrir le livre · ', false);
});

it('sert des icônes de site non vides', function (string $path) {
    /**
     * Le favicon.ico du dépôt a longtemps pesé zéro octet : l'existence du
     * fichier ne suffit pas, il faut vérifier qu'il a du contenu.
     */
    $file = public_path($path);

    expect(file_exists($file))->toBeTrue("$path est absent")
        ->and(filesize($file))->toBeGreaterThan(500, "$path est vide ou tronqué");
})->with([
    'favicon.ico',
    'apple-touch-icon.png',
    'icon-192.png',
    'icon-512.png',
    'site.webmanifest',
]);

it('déclare les icônes et le manifeste dans le layout', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rel="icon"', false)
        ->assertSee('rel="apple-touch-icon"', false)
        ->assertSee('rel="manifest"', false)
        ->assertSee('name="theme-color"', false);
});
