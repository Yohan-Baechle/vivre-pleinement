<?php

use App\Enums\AppointmentStatus;
use App\Enums\CommentStatus;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PostStatus;
use App\Filament\Admin\Resources\Appointments\AppointmentResource;
use App\Filament\Admin\Resources\Comments\CommentResource;
use App\Filament\Admin\Resources\Courses\CourseResource;
use App\Filament\Admin\Resources\Videos\VideoResource;
use App\Filament\Admin\Widgets\CourseSalesStats;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\VideoStatsOverview;
use App\Models\Appointment;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

function widgetPollingInterval(object $widget): ?string
{
    return (function () {
        return $this->getPollingInterval();
    })->call($widget);
}

it('disables polling on the stats overview widgets', function () {
    expect(widgetPollingInterval(new StatsOverview))->toBeNull()
        ->and(widgetPollingInterval(new VideoStatsOverview))->toBeNull()
        ->and(widgetPollingInterval(new CourseSalesStats))->toBeNull();
});

it('shows the actual published/draft article counts on the stats overview widget', function () {
    Post::factory()->count(2)->create(['status' => PostStatus::Published]);
    Post::factory()->create(['status' => PostStatus::Draft]);

    Livewire::test(StatsOverview::class)
        ->assertSee('2')
        ->assertSee('1 brouillon(s)');
});

it('shows the actual pending comment count on the stats overview widget', function () {
    Comment::factory()->create(['status' => CommentStatus::Pending]);
    Comment::factory()->create(['status' => CommentStatus::Approved]);

    Livewire::test(StatsOverview::class)->assertSee('1');
});

it('shows the actual active enrollment count on the course sales stats widget', function () {
    $course = Course::factory()->create();
    Enrollment::factory()->count(3)->create(['course_id' => $course->id, 'status' => EnrollmentStatus::Active]);

    Livewire::test(CourseSalesStats::class)->assertOk();
});

it('caches the appointment/comment/video navigation badges', function () {
    Appointment::factory()->create(['status' => AppointmentStatus::Pending]);
    Comment::factory()->create(['status' => CommentStatus::Pending]);
    Video::factory()->create(['is_missing' => true]);

    expect(AppointmentResource::getNavigationBadge())->toBe('1')
        ->and(CommentResource::getNavigationBadge())->toBe('1')
        ->and(VideoResource::getNavigationBadge())->toBe('1');

    expect(Cache::has('filament.badge.appointments.pending'))->toBeTrue()
        ->and(Cache::has('filament.badge.comments.pending'))->toBeTrue()
        ->and(Cache::has('filament.badge.videos.missing'))->toBeTrue();

    Appointment::factory()->create(['status' => AppointmentStatus::Pending]);

    expect(AppointmentResource::getNavigationBadge())->toBe('1');
});

it('keeps the courses navigation free of a permanent draft badge', function () {
    Course::factory()->create(['status' => CourseStatus::Draft]);

    expect(CourseResource::getNavigationBadge())->toBeNull();
});
