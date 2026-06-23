<?php

use App\Support\Faq;

it('affiche la page FAQ dédiée avec ses questions', function () {
    $response = $this->get(route('faq'));

    $response->assertOk();
    $response->assertSee('Questions fréquentes');
    $response->assertSee(Faq::all()[0]['q']);
    $response->assertSee(Faq::all()[count(Faq::all()) - 1]['q']);
});

it('expose le schema FAQPage en JSON-LD', function () {
    $response = $this->get(route('faq'));

    $response->assertSee('"@type":"FAQPage"', false);
});

it("n'affiche plus la section FAQ sur la page d'accueil", function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('id="faq"', false);
});
