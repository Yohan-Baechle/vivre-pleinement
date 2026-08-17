<?php

use App\Filament\Admin\Resources\DateOverrides\Pages\ListDateOverrides;
use App\Models\DateOverride;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

it('blocks every day of the period in one go', function () {
    $from = CarbonImmutable::now()->addDays(10)->startOfDay();

    Livewire::test(ListDateOverrides::class)
        ->callAction('blockPeriod', [
            'from' => $from->toDateString(),
            'to' => $from->addDays(4)->toDateString(),
            'reason' => 'Congés',
        ]);

    expect(DateOverride::query()->count())->toBe(5)
        ->and(DateOverride::query()->first()->isFullDay())->toBeTrue();
});

it('does not duplicate a day that is already blocked the same way', function () {
    $from = CarbonImmutable::now()->addDays(10)->startOfDay();

    DateOverride::factory()->closed()->create([
        'date' => $from->toDateString(),
    ]);

    Livewire::test(ListDateOverrides::class)
        ->callAction('blockPeriod', [
            'from' => $from->toDateString(),
            'to' => $from->addDay()->toDateString(),
        ]);

    expect(DateOverride::query()->count())->toBe(2);
});

it('keeps the time range when the period is only a partial closure', function () {
    $from = CarbonImmutable::now()->addDays(10)->startOfDay();

    Livewire::test(ListDateOverrides::class)
        ->callAction('blockPeriod', [
            'from' => $from->toDateString(),
            'to' => $from->addDay()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '14:00',
        ]);

    expect(DateOverride::query()->count())->toBe(2)
        ->and(DateOverride::query()->first()->isFullDay())->toBeFalse();
});

it('refuses an end date before the start date', function () {
    $from = CarbonImmutable::now()->addDays(10)->startOfDay();

    Livewire::test(ListDateOverrides::class)
        ->callAction('blockPeriod', [
            'from' => $from->toDateString(),
            'to' => $from->subDays(3)->toDateString(),
        ])
        ->assertHasActionErrors(['to']);

    expect(DateOverride::query()->count())->toBe(0);
});

it('blocks the current day from the quick action', function () {
    Livewire::test(ListDateOverrides::class)
        ->callAction('blockToday');

    expect(DateOverride::query()
        ->whereDate('date', CarbonImmutable::now()->toDateString())
        ->exists())->toBeTrue();
});
