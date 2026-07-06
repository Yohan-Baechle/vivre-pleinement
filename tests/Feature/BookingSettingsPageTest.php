<?php

use App\Filament\Admin\Pages\BookingSettings;
use App\Models\User;
use App\Support\Settings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

it('renders the booking settings page', function () {
    $this->get(route('filament.admin.pages.booking-settings'))->assertOk();
});

it('fills the form from the current settings', function () {
    Settings::setMany([
        'meet_url' => 'https://meet.google.com/abc-defg-hij',
        'notify_email' => 'laura@example.com',
        'reminder_24h_enabled' => '0',
        'reminder_1h_enabled' => '1',
        'followup_enabled' => '0',
    ]);

    Livewire::test(BookingSettings::class)
        ->assertSchemaStateSet([
            'meet_url' => 'https://meet.google.com/abc-defg-hij',
            'notify_email' => 'laura@example.com',
            'reminder_24h_enabled' => false,
            'reminder_1h_enabled' => true,
            'followup_enabled' => false,
        ]);
});

it('persists the settings when the form is saved', function () {
    Livewire::test(BookingSettings::class)
        ->fillForm([
            'meet_url' => 'https://meet.google.com/xyz-uvwx-yz',
            'notify_email' => 'contact@example.com',
            'reminder_24h_enabled' => false,
            'reminder_1h_enabled' => true,
            'followup_enabled' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Settings::get('meet_url'))->toBe('https://meet.google.com/xyz-uvwx-yz')
        ->and(Settings::get('notify_email'))->toBe('contact@example.com')
        ->and(Settings::boolean('reminder_24h_enabled'))->toBeFalse()
        ->and(Settings::boolean('reminder_1h_enabled'))->toBeTrue()
        ->and(Settings::boolean('followup_enabled'))->toBeFalse();
});

it('requires a notification email', function () {
    Livewire::test(BookingSettings::class)
        ->fillForm(['notify_email' => ''])
        ->call('save')
        ->assertHasFormErrors(['notify_email' => 'required']);
});
