<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

it('generates a 301 redirect for every published post from its old WordPress URL', function () {
    Post::factory()->create(['slug' => 'burn-out', 'status' => 'published']);

    $this->artisan('seo:wp-redirects')->assertSuccessful();

    expect(Redirect::where('from_path', '/burn-out')->first())
        ->not->toBeNull()
        ->to_path->toBe('/blog/burn-out')
        ->status_code->toBe(301);
});

it('redirects an old post URL to the new blog URL', function () {
    Post::factory()->create(['slug' => 'burn-out', 'status' => 'published']);
    Redirect::create(['from_path' => '/burn-out', 'to_path' => '/blog/burn-out', 'status_code' => 301]);

    $this->get('/burn-out')->assertRedirect('/blog/burn-out')->assertStatus(301);
});

it('redirects regardless of a trailing slash (as indexed by Google)', function () {
    Redirect::create(['from_path' => '/burn-out', 'to_path' => '/blog/burn-out', 'status_code' => 301]);

    $this->get('/burn-out/')->assertRedirect('/blog/burn-out')->assertStatus(301);
});

it('maps old category URLs to the new structure', function () {
    Category::factory()->create(['slug' => 'angoisse-et-anxiete']);

    $this->artisan('seo:wp-redirects')->assertSuccessful();

    expect(Redirect::where('from_path', '/category/angoisse-et-anxiete')->first())
        ->not->toBeNull()
        ->to_path->toBe('/blog/categorie/angoisse-et-anxiete');
});

it('maps the booking page from its old WordPress slug', function () {
    $this->artisan('seo:wp-redirects')->assertSuccessful();

    expect(Redirect::where('from_path', '/prendre-rendez-vous')->first())
        ->not->toBeNull()
        ->to_path->toBe('/reservation');
});

it('maps the old WooCommerce product categories to the book page', function () {
    $this->artisan('seo:wp-redirects')->assertSuccessful();

    expect(Redirect::where('from_path', '/categorie-produit/ebook')->first())
        ->not->toBeNull()
        ->to_path->toBe('/livre')
        ->and(Redirect::where('from_path', '/categorie-produit/ebook-coaching')->first())
        ->not->toBeNull()
        ->to_path->toBe('/livre');
});

it('maps WordPress blog pagination and the indexed legacy image', function () {
    $this->artisan('seo:wp-redirects')->assertSuccessful();

    expect(Redirect::where('from_path', '/blog/page/3')->first())
        ->not->toBeNull()
        ->to_path->toBe('/blog?page=3')
        ->and(Redirect::where('from_path', '/wp-content/uploads/2020/10/Liberer-pression-sociale.jpg')->first())
        ->not->toBeNull()
        ->to_path->toBe('/blog/normes-sociales');
});

it('redirects a paginated WordPress URL even with its Divi query string', function () {
    Redirect::create(['from_path' => '/blog/page/3', 'to_path' => '/blog?page=3', 'status_code' => 301]);

    $this->get('/blog/page/3/?et_blog')->assertStatus(301)->assertRedirect(url('/blog?page=3'));
});

it('preserves the URL fragment when redirecting to an internal anchor', function () {
    Redirect::create(['from_path' => '/ancienne-page', 'to_path' => '/#a-propos', 'status_code' => 301]);

    $this->get('/ancienne-page')
        ->assertStatus(301)
        ->assertRedirect(url('/#a-propos'));
});

it('tracks hit_count and last_hit_at in a single update query', function () {
    $redirect = Redirect::create(['from_path' => '/burn-out', 'to_path' => '/blog/burn-out', 'status_code' => 301]);

    DB::enableQueryLog();
    $this->get('/burn-out');
    $log = DB::getQueryLog();
    DB::disableQueryLog();

    $updateQueries = collect($log)->filter(fn ($q) => str_starts_with(strtolower($q['query']), 'update')
        && (str_contains($q['query'], 'redirects')));

    expect($updateQueries)->toHaveCount(1);

    $fresh = $redirect->fresh();
    expect($fresh->hit_count)->toBe(1)
        ->and($fresh->last_hit_at)->not->toBeNull();
});

it('does not redirect an unknown URL', function () {
    $this->get('/cette-url-nexiste-pas')->assertNotFound();
});

it('is idempotent and supports --fresh', function () {
    Post::factory()->create(['slug' => 'burn-out', 'status' => 'published']);

    $this->artisan('seo:wp-redirects')->assertSuccessful();
    $countAfterFirst = Redirect::count();

    $this->artisan('seo:wp-redirects')->assertSuccessful();
    expect(Redirect::count())->toBe($countAfterFirst);

    $this->artisan('seo:wp-redirects', ['--fresh' => true])->assertSuccessful();
    expect(Redirect::count())->toBe($countAfterFirst);
});
