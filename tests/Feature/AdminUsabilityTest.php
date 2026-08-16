<?php

use App\Enums\CommentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PostStatus;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Resources\Comments\CommentResource;
use App\Filament\Admin\Resources\Posts\Pages\EditPost;
use App\Filament\Admin\Resources\Posts\Pages\ListPosts;
use App\Filament\Admin\Resources\Redirects\RedirectResource;
use App\Filament\Admin\Resources\Students\Pages\ViewStudent;
use App\Filament\Admin\Resources\Students\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Admin\Resources\Tags\TagResource;
use App\Mail\CourseAccessGranted;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

it('groups the navigation into four sections without a lone entry', function () {
    $groups = collect(Filament::getPanel('admin')->getNavigationGroups())
        ->map(fn ($group) => is_string($group) ? $group : $group->getLabel())
        ->values()
        ->all();

    expect($groups)->toBe([
        'Rendez-vous',
        'Contenu',
        'Boutique',
        'Réglages du site',
    ]);
});

it('tells the admin what is waiting on the dashboard', function () {
    Comment::factory()->create(['status' => CommentStatus::Pending]);

    $subheading = Livewire::test(Dashboard::class)
        ->instance()
        ->getSubheading();

    expect($subheading)->toContain('1 commentaire à modérer');
});

it('says nothing is waiting when the queues are empty', function () {
    expect(Livewire::test(Dashboard::class)->instance()->getSubheading())
        ->toBe('Rien ne vous attend : tout est traité.');
});

it('publishes several articles at once and keeps the observer in the loop', function () {
    $posts = Post::factory()->count(2)->create([
        'status' => PostStatus::Draft,
        'published_at' => null,
    ]);

    Livewire::test(ListPosts::class)
        ->selectTableRecords($posts->pluck('id')->all())
        ->callAction(TestAction::make('publish')->table()->bulk());

    expect(Post::query()->where('status', PostStatus::Published)->count())->toBe(2)
        ->and(Post::query()->whereNull('published_at')->count())->toBe(0);
});

it('redirects the old address when a published article is renamed', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::Published,
        'slug' => 'ancienne-adresse',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['slug' => 'nouvelle-adresse'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Redirect::query()
        ->where('from_path', '/blog/ancienne-adresse')
        ->where('to_path', '/blog/nouvelle-adresse')
        ->where('status_code', 301)
        ->exists())->toBeTrue();
});

it('leaves a draft rename alone since no address was ever public', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::Draft,
        'slug' => 'brouillon',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['slug' => 'brouillon-renomme'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Redirect::query()->count())->toBe(0);
});

it('previews a draft through a signed url and keeps it out of the index', function () {
    $post = Post::factory()->create([
        'status' => PostStatus::Draft,
        'slug' => 'brouillon-a-relire',
    ]);

    $this->get($post->previewUrl())
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee($post->title, escape: false);
});

it('refuses an unsigned preview url', function () {
    $post = Post::factory()->create(['status' => PostStatus::Draft]);

    $this->get(route('blog.preview', ['post' => $post->getRouteKey()]))
        ->assertForbidden();
});

it('offers a course to a student and opens the access straight away', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();

    Livewire::test(EnrollmentsRelationManager::class, [
        'ownerRecord' => $student,
        'pageClass' => ViewStudent::class,
    ])
        ->callAction(TestAction::make('grantAccess')->table(), [
            'course_id' => $course->id,
            'notify' => true,
        ]);

    $enrollment = Enrollment::query()->firstWhere('student_id', $student->id);

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toBe(EnrollmentStatus::Active)
        ->and($enrollment->amount_paid_cents)->toBe(0)
        ->and($student->fresh()->hasAccessTo($course))->toBeTrue();

    Mail::assertQueued(CourseAccessGranted::class);
});

it('never offers a course the student already has access to', function () {
    $student = Student::factory()->create();
    $owned = Course::factory()->create();

    Enrollment::factory()->create([
        'student_id' => $student->id,
        'course_id' => $owned->id,
        'status' => EnrollmentStatus::Active,
    ]);

    Livewire::test(EnrollmentsRelationManager::class, [
        'ownerRecord' => $student,
        'pageClass' => ViewStudent::class,
    ])
        ->callAction(TestAction::make('grantAccess')->table(), [
            'course_id' => $owned->id,
            'notify' => false,
        ])
        ->assertHasActionErrors(['course_id']);

    expect(Enrollment::query()->where('student_id', $student->id)->count())->toBe(1);
});

it('exposes the overlooked resources to the global search', function () {
    expect(TagResource::getGloballySearchableAttributes())->toContain('name')
        ->and(RedirectResource::getGloballySearchableAttributes())->toContain('from_path')
        ->and(CommentResource::getGloballySearchableAttributes())->toContain('author_name');
});

it('opens every admin screen without an error', function (string $route) {
    $this->get(route($route))->assertOk();
})->with([
    'filament.admin.pages.dashboard',
    'filament.admin.pages.weekly-schedule',
    'filament.admin.pages.booking-settings',
    'filament.admin.pages.contact-settings',
    'filament.admin.resources.appointments.index',
    'filament.admin.resources.appointment-services.index',
    'filament.admin.resources.date-overrides.index',
    'filament.admin.resources.availabilities.index',
    'filament.admin.resources.posts.index',
    'filament.admin.resources.videos.index',
    'filament.admin.resources.comments.index',
    'filament.admin.resources.categories.index',
    'filament.admin.resources.tags.index',
    'filament.admin.resources.courses.index',
    'filament.admin.resources.products.index',
    'filament.admin.resources.students.index',
    'filament.admin.resources.enrollments.index',
    'filament.admin.resources.redirects.index',
]);

it('gives every empty table an icon to go with its message', function () {
    $missing = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Filament')),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (str_contains($contents, '->emptyStateHeading(')
            && ! str_contains($contents, '->emptyStateIcon(')) {
            $missing[] = basename($file->getPathname());
        }
    }

    expect($missing)->toBe([]);
});

it('gives every navigation group an icon', function () {
    $withoutIcon = collect(Filament::getPanel('admin')->getNavigationGroups())
        ->filter(fn ($group) => is_string($group) || blank($group->getIcon()))
        ->map(fn ($group) => is_string($group) ? $group : $group->getLabel())
        ->values()
        ->all();

    expect($withoutIcon)->toBe([]);
});

it('keeps emoji out of the interface chrome', function () {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Filament')),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (preg_match('/[\x{26A0}\x{2709}\x{2713}\x{1F300}-\x{1FAFF}]/u', $contents)) {
            $offenders[] = basename($file->getPathname());
        }
    }

    expect($offenders)->toBe([]);
});
