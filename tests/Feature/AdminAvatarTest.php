<?php

use App\Filament\AvatarProviders\InitialsAvatarProvider;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(fn () => Filament::setCurrentPanel('admin'));

it('builds the avatar locally so the content security policy never blocks it', function () {
    $user = User::factory()->create(['name' => 'Laura Martin']);

    $avatar = app(InitialsAvatarProvider::class)->get($user);

    expect($avatar)->toStartWith('data:image/svg+xml;base64,');

    $svg = base64_decode(substr($avatar, strlen('data:image/svg+xml;base64,')));

    expect($svg)->toContain('LM')
        ->and($svg)->not->toContain('ui-avatars.com');
});

it('falls back to a single character when the name is one word', function () {
    $user = User::factory()->create(['name' => 'Laura']);

    $svg = base64_decode(substr(
        app(InitialsAvatarProvider::class)->get($user),
        strlen('data:image/svg+xml;base64,'),
    ));

    expect($svg)->toContain('>L<');
});

it('registers the local avatar provider on the admin panel', function () {
    $user = User::factory()->create(['name' => 'Laura Martin']);

    expect(Filament::getUserAvatarUrl($user))->toStartWith('data:image/svg+xml;base64,');
});
