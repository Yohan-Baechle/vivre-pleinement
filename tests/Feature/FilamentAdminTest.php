<?php

use App\Models\Appointment;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('redirects a guest away from the admin panel', function () {
    auth()->logout();

    $this->get(route('filament.admin.pages.dashboard'))->assertRedirect();
});

it('renders the dashboard with its widgets', function () {
    Post::factory()->count(2)->create();
    Comment::factory()->create();

    $this->get(route('filament.admin.pages.dashboard'))->assertOk();
});

it('renders the posts list and edit page', function () {
    $post = Post::factory()->create();

    $this->get(route('filament.admin.resources.posts.index'))->assertOk();
    $this->get(route('filament.admin.resources.posts.edit', $post))->assertOk();
});

it('renders the videos list and edit page', function () {
    $video = Video::factory()->create();

    $this->get(route('filament.admin.resources.videos.index'))->assertOk();
    $this->get(route('filament.admin.resources.videos.edit', $video))->assertOk();
});

it('renders the comments list and edit page', function () {
    $comment = Comment::factory()->create();

    $this->get(route('filament.admin.resources.comments.index'))->assertOk();
    $this->get(route('filament.admin.resources.comments.edit', $comment))->assertOk();
});

it('renders the appointments list and edit page', function () {
    $appointment = Appointment::factory()->create();

    $this->get(route('filament.admin.resources.appointments.index'))->assertOk();
    $this->get(route('filament.admin.resources.appointments.edit', $appointment))->assertOk();
});
