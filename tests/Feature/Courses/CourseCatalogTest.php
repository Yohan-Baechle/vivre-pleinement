<?php

use App\Models\Course;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('liste uniquement les formations publiées dans le catalogue', function () {
    $published = Course::factory()->create(['title' => 'Apaiser son anxiété']);
    $draft = Course::factory()->draft()->create(['title' => 'Brouillon caché']);

    $this->get(route('courses.index'))
        ->assertOk()
        ->assertSee('Apaiser son anxiété')
        ->assertDontSee('Brouillon caché');
});

it('affiche la page de vente d\'une formation publiée', function () {
    $course = Course::factory()->create(['slug' => 'apaiser-anxiete', 'title' => 'Apaiser son anxiété']);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('Apaiser son anxiété');
});

it('rend le seo_title tel quel, sans suffixe « · Formation » dupliqué', function () {
    $course = Course::factory()->create([
        'slug' => 'apaiser-anxiete',
        'title' => 'Apaiser son anxiété au quotidien',
        'seo_title' => 'Formation : apaiser son anxiété au quotidien',
    ]);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('<title>Formation : apaiser son anxiété au quotidien</title>', false)
        ->assertDontSee('au quotidien · Formation', false);
});

it('suffixe le titre avec « · Formation » quand seo_title est vide', function () {
    $course = Course::factory()->create([
        'slug' => 'sans-seo-title',
        'title' => 'Une formation test',
        'seo_title' => null,
    ]);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('<title>Une formation test · Formation</title>', false);
});

it('renvoie 404 pour une formation en brouillon', function () {
    $course = Course::factory()->draft()->create();

    $this->get(route('courses.show', $course))->assertNotFound();
});

it('renvoie 404 pour une formation publiée avec une date de publication future', function () {
    $course = Course::factory()->create(['published_at' => now()->addWeek()]);

    $this->get(route('courses.show', $course))->assertNotFound();
});
