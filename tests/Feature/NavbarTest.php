<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('expose l\'accompagnement dans le menu principal', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Accompagnement')
        ->assertSee(route('booking.index'));
});

it('affiche les trois offres et les trois entrées de découverte', function () {
    $response = $this->get(route('home'))->assertOk();

    foreach (['Accompagnement', 'Formations', 'Le livre', 'Blog', 'Vidéos', 'À propos'] as $label) {
        $response->assertSee($label, false);
    }
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
