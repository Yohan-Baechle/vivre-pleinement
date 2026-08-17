<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('promotes the flagship course at the end of an anxiety article', function () {
    $course = Course::factory()->create(['title' => 'Apaiser son anxiété au quotidien']);
    $category = Category::query()->firstOrCreate(['slug' => 'anxiete-et-angoisses'], ['name' => 'Anxiété']);
    $post = Post::factory()->create(['status' => 'published']);
    $post->categories()->attach($category);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Pour aller plus loin')
        ->assertSee(route('courses.show', $course), false)
        ->assertSee(route('booking.index'), false);
});

it('promotes the book on TOC and intrusive thoughts articles', function () {
    Course::factory()->create();
    $category = Category::query()->firstOrCreate(['slug' => 'toc-et-pensees-intrusives'], ['name' => 'TOC']);
    $post = Post::factory()->create(['status' => 'published']);
    $post->categories()->attach($category);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee(route('book.show'), false)
        ->assertSee('Découvrir le livre');
});

it('falls back to the book when no course is published', function () {
    Course::factory()->draft()->create();
    $post = Post::factory()->create(['status' => 'published']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Pour aller plus loin')
        ->assertSee(route('book.show'), false);
});

it('shows the contextual CTA on video pages', function () {
    $course = Course::factory()->create();
    $video = Video::factory()->create();

    $this->get(route('videos.show', $video->slug))
        ->assertOk()
        ->assertSee('Pour aller plus loin')
        ->assertSee(route('courses.show', $course), false);
});
