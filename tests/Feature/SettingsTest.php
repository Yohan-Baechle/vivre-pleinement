<?php

use App\Models\Setting;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(fn () => Settings::flush());

it('returns the default when a setting is missing', function () {
    expect(Settings::get('unknown', 'fallback'))->toBe('fallback');
});

it('persists and reads a setting', function () {
    Settings::set('meet_url', 'https://meet.google.com/abc-defg-hij');

    expect(Settings::get('meet_url'))->toBe('https://meet.google.com/abc-defg-hij')
        ->and(Setting::query()->where('key', 'meet_url')->value('value'))->toBe('https://meet.google.com/abc-defg-hij');
});

it('casts booleans correctly', function () {
    Settings::set('reminder_24h_enabled', '1');
    Settings::set('reminder_1h_enabled', '0');

    expect(Settings::boolean('reminder_24h_enabled'))->toBeTrue()
        ->and(Settings::boolean('reminder_1h_enabled'))->toBeFalse()
        ->and(Settings::boolean('missing', true))->toBeTrue();
});

it('invalidates the cache on write', function () {
    Settings::set('meet_url', 'old');
    expect(Settings::get('meet_url'))->toBe('old'); // primes cache

    Settings::set('meet_url', 'new');
    expect(Settings::get('meet_url'))->toBe('new');
});

it('caches reads under a single key', function () {
    Settings::set('notify_email', 'a@gmail.com');
    Settings::all();

    expect(Cache::has('settings.all'))->toBeTrue();
});

it('persists several settings at once', function () {
    Settings::setMany([
        'meet_url' => 'https://meet.google.com/abc-defg-hij',
        'notify_email' => 'pro@gmail.com',
        'empty' => null,
    ]);

    expect(Settings::get('meet_url'))->toBe('https://meet.google.com/abc-defg-hij')
        ->and(Settings::get('notify_email'))->toBe('pro@gmail.com')
        ->and(Settings::get('empty'))->toBeNull();
});
