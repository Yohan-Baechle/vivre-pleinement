<?php

use App\Enums\CommentStatus;
use App\Filament\Admin\Resources\Comments\Pages\ListComments;
use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

it('approves several comments at once via the bulk action', function () {
    $comments = Comment::factory()->count(3)->create(['status' => CommentStatus::Pending]);

    Livewire::test(ListComments::class)
        ->selectTableRecords($comments->pluck('id')->map(fn ($id) => (string) $id)->all())
        ->callAction(TestAction::make('approveAll')->table()->bulk());

    expect(Comment::where('status', CommentStatus::Approved)->count())->toBe(3);
});

it('escapes html in the comment content column instead of rendering it', function () {
    Comment::factory()->create(['content' => '<strong>bonjour</strong>']);

    Livewire::test(ListComments::class)
        ->assertDontSee('<strong>bonjour</strong>', false)
        ->assertSee('<strong>bonjour</strong>');
});
