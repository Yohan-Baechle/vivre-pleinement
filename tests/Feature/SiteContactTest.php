<?php

use App\Filament\Admin\Pages\ContactSettings;
use App\Models\User;
use App\Support\Settings;
use App\Support\SiteContact;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('falls back to the config email when no contact email is set', function () {
    expect(SiteContact::email())->toBe(config('mail.contact_to'));
});

it('returns the configured contact email', function () {
    Settings::set('contact_email', 'laura@example.com');

    expect(SiteContact::email())->toBe('laura@example.com');
});

it('returns null for an empty phone and a cleaned href otherwise', function () {
    expect(SiteContact::phone())->toBeNull()
        ->and(SiteContact::phoneHref())->toBeNull();

    Settings::set('contact_phone', '06 12 34 56 78');

    expect(SiteContact::phone())->toBe('06 12 34 56 78')
        ->and(SiteContact::phoneHref())->toBe('0612345678');
});

it('only returns social links that are filled', function () {
    Settings::setMany([
        'social_instagram' => 'https://instagram.com/laura',
        'social_facebook' => '',
        'social_youtube' => 'https://youtube.com/@laura',
        'social_tiktok' => '',
    ]);

    expect(SiteContact::socials())->toBe([
        'Instagram' => 'https://instagram.com/laura',
        'YouTube' => 'https://youtube.com/@laura',
    ]);
});

it('hides the social block on the contact page when none are set', function () {
    Settings::setMany([
        'social_instagram' => '',
        'social_facebook' => '',
        'social_youtube' => '',
        'social_tiktok' => '',
    ]);

    $this->get(route('contact'))
        ->assertOk()
        ->assertDontSee('Sur les réseaux');
});

it('shows configured social links on the contact page', function () {
    Settings::set('social_instagram', 'https://instagram.com/laura');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Sur les réseaux')
        ->assertSee('https://instagram.com/laura');
});

it('renders and saves the contact settings admin page', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');

    Livewire::test(ContactSettings::class)
        ->assertOk()
        ->set('data.contact_email', 'laura@example.com')
        ->set('data.contact_phone', '06 12 34 56 78')
        ->set('data.social_instagram', 'https://instagram.com/laura')
        ->call('save')
        ->assertHasNoErrors();

    expect(Settings::get('contact_email'))->toBe('laura@example.com')
        ->and(Settings::get('contact_phone'))->toBe('06 12 34 56 78')
        ->and(Settings::get('social_instagram'))->toBe('https://instagram.com/laura');
});
