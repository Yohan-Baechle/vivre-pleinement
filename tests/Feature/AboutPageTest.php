<?php

it('affiche la page Qui suis-je', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Qui suis-je')
        ->assertSee('praticienne ACT');
});

it('répond sur le slug /a-propos', function () {
    $this->get('/a-propos')->assertOk();
});
