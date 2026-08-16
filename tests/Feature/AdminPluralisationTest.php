<?php

use App\Enums\AppointmentStatus;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Pages\WeeklySchedule;
use App\Filament\Admin\Resources\Comments\Pages\ListComments;
use App\Filament\Admin\Resources\DateOverrides\Pages\ListDateOverrides;
use App\Filament\Admin\Resources\Posts\Pages\ListPosts;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\CarbonImmutable;
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
 * Laravel n'accepte que les intervalles entre accolades ou crochets fermants
 * ({1}, [2,*]). La notation exclusive ]1,*[ empruntée à ICU ne correspond à
 * aucune de ses expressions régulières : le segment n'est ni choisi ni
 * nettoyé, et l'admin lit « ]1,*[ 5 créneaux » à l'écran dès que le compte
 * dépasse un. Ce test interdit la notation à la source.
 */
it('never uses an interval syntax Laravel cannot strip', function () {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path()),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (str_contains($contents, ']1,*[') || preg_match('/\]\d+,/', $contents)) {
            $offenders[] = $file->getPathname();
        }
    }

    expect($offenders)->toBe([]);
});

it('pluralises the dashboard summary beyond one', function () {
    Comment::factory()->count(3)->create(['status' => CommentStatus::Pending]);
    Appointment::factory()->count(2)->create([
        'status' => AppointmentStatus::Pending,
    ]);

    $subheading = Livewire::test(Dashboard::class)->instance()->getSubheading();

    expect($subheading)
        ->toBe('À traiter aujourd\'hui : 2 rendez-vous à confirmer et '
            .'3 commentaires à modérer.')
        ->and($subheading)->not->toContain('[');
});

it('pluralises the bulk publish notification beyond one', function () {
    $posts = Post::factory()->count(3)->create([
        'status' => PostStatus::Draft,
        'published_at' => null,
    ]);

    Livewire::test(ListPosts::class)
        ->selectTableRecords($posts->pluck('id')->all())
        ->callAction(TestAction::make('publish')->table()->bulk())
        ->assertNotified('3 articles publiés');
});

it('pluralises the bulk comment approval notification beyond one', function () {
    $comments = Comment::factory()->count(2)->create([
        'status' => CommentStatus::Pending,
    ]);

    Livewire::test(ListComments::class)
        ->selectTableRecords($comments->pluck('id')->all())
        ->callAction(TestAction::make('approveAll')->table()->bulk())
        ->assertNotified('2 commentaires publiés');
});

it('pluralises the blocked period notification beyond one', function () {
    $from = CarbonImmutable::now()->addDays(10)->startOfDay();

    Livewire::test(ListDateOverrides::class)
        ->callAction('blockPeriod', [
            'from' => $from->toDateString(),
            'to' => $from->addDays(4)->toDateString(),
        ])
        ->assertNotified('5 journées bloquées');
});

it('pluralises the saved schedule notification beyond one', function () {
    Livewire::test(WeeklySchedule::class)
        ->fillForm(scheduleFormState([
            1 => [
                ['start_time' => '09:00', 'end_time' => '12:00'],
                ['start_time' => '14:00', 'end_time' => '18:00'],
            ],
        ]))
        ->call('save')
        ->assertNotified();

    expect(Availability::query()->count())->toBe(2);
});

it('shows a readable slot count on the weekly schedule', function () {
    AppointmentService::factory()->create([
        'name' => 'Accompagnement ACT',
        'duration_minutes' => 60,
        'buffer_minutes' => 0,
        'is_active' => true,
    ]);

    Availability::factory()->create([
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '14:00',
    ]);

    $html = Livewire::test(WeeklySchedule::class)->html();

    expect($html)->toContain('5 créneaux de 60 min')
        ->and($html)->not->toContain(']1,*[')
        ->and($html)->not->toContain('[2,*]');
});
