<?php

namespace App\Filament\Admin\Resources\Videos\Pages;

use App\Enums\VideoStatus;
use App\Filament\Admin\Resources\Videos\VideoResource;
use App\Models\Video;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_on_site')
                ->label('Voir sur le site')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn (Video $record) => route('videos.show', $record))
                ->openUrlInNewTab()
                ->visible(fn (Video $record) => ! $record->is_missing && $record->status === VideoStatus::Published),

            Action::make('open_youtube')
                ->label('Ouvrir sur YouTube')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Video $record) => $record->youtubeUrl())
                ->openUrlInNewTab(),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
