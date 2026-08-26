<?php

use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('expose l\'accompagnement dans le menu principal', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Accompagnement')
        ->assertSee(route('booking.index'));
});

it('affiche les trois offres et les trois entrées de découverte', function () {
    Course::factory()->create();

    $response = $this->get(route('home'))->assertOk();

    foreach (['Accompagnement', 'Formations', 'Le livre', 'Blog', 'Vidéos', 'À propos'] as $label) {
        $response->assertSee($label, false);
    }
});

/**
 * L'espace élève ne sert qu'aux formations : sans formation publiée, ces deux
 * entrées mènent à un catalogue vide et à un compte sans contenu.
 */
it('retire les formations et la connexion du menu quand rien n\'est publié', function () {
    Course::query()->forceDelete();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Formations', false)
        ->assertDontSee(route('courses.index'), false)
        ->assertDontSee(route('student.login'), false);
});

it('rend les formations et la connexion au menu dès la première publication', function () {
    Course::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Formations', false)
        ->assertSee(route('courses.index'), false)
        ->assertSee(route('student.login'), false);
});

it('garde le menu de l\'élève connecté même sans formation publiée', function () {
    $this->actingAs(Student::factory()->create(), 'student')
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('student.dashboard'), false);
});

it('sort « Me contacter » du menu au profit du footer', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect(substr_count($html, 'Me contacter'))->toBe(0);
    expect($html)->toContain(route('contact'));
});

it('envoie le bouton d\'appel à l\'action vers le formulaire de réservation', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('booking.index').'#reserver');
});

it('marque l\'entrée accompagnement comme active sur la page de réservation', function () {
    $this->get(route('booking.index'))
        ->assertOk()
        ->assertSee('aria-current="page"', false);
});

it('conserve une ancre de réservation sur la page accompagnement', function () {
    $this->get(route('booking.index'))
        ->assertOk()
        ->assertSee('id="reserver"', false);
});
