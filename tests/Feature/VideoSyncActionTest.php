<?php

use App\Filament\Admin\Resources\Videos\Pages\ListVideos;
use App\Jobs\SyncYoutubeVideosJob;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('dispatches the youtube sync job onto the queue instead of running it inline', function () {
    Queue::fake();

    Livewire::test(ListVideos::class)
        ->callAction(TestAction::make('sync'))
        ->assertHasNoActionErrors();

    Queue::assertPushed(SyncYoutubeVideosJob::class);
});
