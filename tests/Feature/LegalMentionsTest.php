<?php

declare(strict_types=1);

it('affiche l\'hébergeur déclaré dans la configuration légale', function (): void {
    $this->get(route('legal.mentions'))
        ->assertOk()
        ->assertSee(config('legal.host.name'))
        ->assertSee(config('legal.host.address'));
});

it('affiche la mention de franchise en base de TVA', function (): void {
    $this->get(route('legal.mentions'))
        ->assertOk()
        ->assertSee('TVA non applicable, article 293 B du CGI');
});

it('declare OVH comme hébergeur et la franchise en base', function (): void {
    expect(config('legal.host.name'))->toBe('OVH SAS')
        ->and(config('legal.editor.vat'))
        ->toBe('TVA non applicable, article 293 B du CGI');
});
