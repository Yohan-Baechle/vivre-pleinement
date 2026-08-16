<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Admin\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\Redirect;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    /**
     * Slug tel qu'il était à l'ouverture du formulaire, pour détecter un
     * renommage au moment de l'enregistrement.
     */
    private ?string $slugBeforeSave = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_on_site')
                ->label('Voir sur le site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('blog.show', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === PostStatus::Published),
            Action::make('preview_draft')
                ->label('Prévisualiser')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => $this->record->previewUrl())
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status !== PostStatus::Published),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->slugBeforeSave = $this->record->slug;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->redirectRenamedPost();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Article enregistré')
            ->body('Tes modifications ont bien été sauvegardées.');
    }

    /**
     * Renommer un article publié casse son adresse : la redirection 301 est
     * créée d'office pour que les liens et le référencement suivent.
     */
    private function redirectRenamedPost(): void
    {
        /** @var Post $post */
        $post = $this->record;
        $previous = $this->slugBeforeSave;

        if (blank($previous) || $previous === $post->slug) {
            return;
        }

        if ($post->status !== PostStatus::Published) {
            return;
        }

        $from = '/blog/'.$previous;

        if (Redirect::query()->where('from_path', $from)->exists()) {
            return;
        }

        Redirect::create([
            'from_path' => $from,
            'to_path' => '/blog/'.$post->slug,
            'status_code' => 301,
        ]);

        Notification::make()
            ->success()
            ->title('Ancienne adresse redirigée')
            ->body("Les visiteurs et Google arrivant sur {$from} sont "
                .'renvoyés vers la nouvelle adresse.')
            ->send();
    }
}
