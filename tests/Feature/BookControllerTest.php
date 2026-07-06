<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders the book landing page', function () {
    $this->get(route('book.show'))->assertOk();
});

it('renders the checkout-soon page for the book-only offer', function () {
    $this->get(route('book.checkout', 'livre'))
        ->assertOk()
        ->assertSee('Le livre seul');
});

it('renders the checkout-soon page for the book-plus-coaching offer', function () {
    $this->get(route('book.checkout', 'livre-coaching'))
        ->assertOk()
        ->assertSee('Le livre + coaching');
});

it('404s for an unknown offer', function () {
    $this->get('/livre/commande/unknown-offer')->assertNotFound();
});
