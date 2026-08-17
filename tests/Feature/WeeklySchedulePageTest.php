<?php

use App\Filament\Admin\Pages\WeeklySchedule;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\User;
use App\Support\Weekdays;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

/**
 * Construit un état de formulaire complet : tous les jours fermés, sauf ceux
 * décrits par $openDays (clé = dayOfWeek Carbon, valeur = liste de plages).
 *
 * @param  array<int, array<int, array{start_time: string, end_time: string}>>  $openDays
 * @return array<string, mixed>
 */
function scheduleFormState(array $openDays, ?int $serviceId = null): array
{
    $days = [];

    foreach (Weekdays::orderedKeys() as $day) {
        $days["day_{$day}"] = [
            'is_open' => isset($openDays[$day]),
            'ranges' => $openDays[$day] ?? [],
        ];
    }

    return [
        'appointment_service_id' => $serviceId,
        'days' => $days,
    ];
}

it('renders the weekly schedule page', function () {
    $this->get(route('filament.admin.pages.weekly-schedule'))->assertOk();
});

it('loads the existing availabilities into the weekly form', function () {
    Availability::factory()->create([
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '12:00',
    ]);

    Livewire::test(WeeklySchedule::class)
        ->assertSchemaStateSet([
            'days.day_1.is_open' => true,
            'days.day_2.is_open' => false,
        ]);
});

it('saves every open day as one availability per time range', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [
                ['start_time' => '09:00', 'end_time' => '12:00'],
                ['start_time' => '14:00', 'end_time' => '18:00'],
            ],
            3 => [
                ['start_time' => '10:00', 'end_time' => '13:00'],
            ],
        ]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Availability::query()->count())->toBe(3)
        ->and(Availability::query()->where('day_of_week', 1)->count())->toBe(2)
        ->and(Availability::query()->where('day_of_week', 3)->count())->toBe(1)
        ->and(Availability::query()->where('is_active', false)->count())->toBe(0);
});

it('replaces the previous availabilities of the same scope', function () {
    Availability::factory()->create([
        'day_of_week' => 5,
        'start_time' => '08:00',
        'end_time' => '09:00',
    ]);

    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [['start_time' => '09:00', 'end_time' => '12:00']],
        ]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Availability::query()->where('day_of_week', 5)->exists())
        ->toBeFalse()
        ->and(Availability::query()->where('day_of_week', 1)->count())->toBe(1);
});

it('leaves the availabilities of other services untouched', function () {
    $service = AppointmentService::factory()->create();

    Availability::factory()->forService($service)->create([
        'day_of_week' => 2,
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [['start_time' => '09:00', 'end_time' => '12:00']],
        ]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Availability::query()
        ->where('appointment_service_id', $service->id)
        ->count())->toBe(1);
});

it('keeps the ranges of a closed day but deactivates them', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [['start_time' => '09:00', 'end_time' => '12:00']],
        ]))
        ->set('data.days.day_1.is_open', false)
        ->call('save')
        ->assertHasNoFormErrors();

    $availability = Availability::query()->where('day_of_week', 1)->first();

    expect($availability)->not->toBeNull()
        ->and($availability->is_active)->toBeFalse();
});

it('refuses two overlapping ranges on the same day', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [
                ['start_time' => '09:00', 'end_time' => '12:00'],
                ['start_time' => '11:00', 'end_time' => '13:00'],
            ],
        ]))
        ->call('save')
        ->assertHasFormErrors(['days.day_1.ranges']);

    expect(Availability::query()->count())->toBe(0);
});

it('copies the ranges of one day onto the days that were picked', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [['start_time' => '09:00', 'end_time' => '12:00']],
        ]))
        ->callAction(
            TestAction::make('copy_day_1')->schemaComponent(),
            ['targets' => [2, 3]],
        )
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Availability::query()->where('day_of_week', 2)->count())->toBe(1)
        ->and(Availability::query()->where('day_of_week', 3)->count())->toBe(1);
});

it('applies a preset schedule to the selected days', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([]))
        ->mountAction('applyPreset')
        ->set('mountedActions.0.data.days', [1, 2])
        ->callMountedAction()
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Availability::query()->count())->toBe(4)
        ->and(Availability::query()->where('day_of_week', 1)->count())->toBe(2);
});
