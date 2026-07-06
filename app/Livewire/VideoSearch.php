<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Video;
use App\Support\LikeSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class VideoSearch extends Component
{
    use WithPagination;

    private const PER_PAGE = 12;

    #[Url(as: 'q')]
    #[Validate('string|max:100')]
    public string $search = '';

    #[Url]
    #[Validate('string|max:100')]
    public string $category = '';

    /**
     * Tronque défensivement les valeurs venues de l'URL : #[Validate] ne
     * s'applique qu'aux mises à jour ultérieures via wire:model, pas à
     * l'hydratation initiale depuis la query string.
     */
    public function mount(): void
    {
        $this->search = mb_substr($this->search, 0, 100);
        $this->category = mb_substr($this->category, 0, 100);
    }

    /**
     * Réinitialise la pagination dès que la recherche change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Réinitialise la pagination dès que le filtre de catégorie change.
     */
    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $slug): void
    {
        $this->category = $this->category === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Catégories ayant au moins une vidéo publiée, avec leur compteur.
     *
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->whereHas('videos', fn (Builder $q) => $q->published())
            ->withCount(['videos' => fn (Builder $q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    /**
     * Nombre total de vidéos publiées (pour le bouton « Toutes »).
     */
    #[Computed]
    public function totalVideos(): int
    {
        return Video::query()->published()->count();
    }

    /**
     * La recherche filtre sur title/summary/intro sans les charger : la carte
     * vidéo n'affiche que les colonnes listées dans le paginate() ci-dessous,
     * intro et transcript (longText) n'y sont jamais nécessaires.
     * La collation utf8mb4_unicode_ci de MariaDB est insensible à la casse
     * ET aux accents : « depersonnalisation » trouve « dépersonnalisation ».
     */
    public function render(): View
    {
        $term = trim($this->search);

        $videos = Video::query()
            ->published()
            ->with('categories')
            ->when($this->category !== '', fn (Builder $q) => $q->whereHas(
                'categories',
                fn (Builder $cq) => $cq->where('slug', $this->category),
            ))
            ->when($term !== '', function (Builder $q) use ($term): void {
                $like = LikeSearch::wrap($term);

                $q->where(function (Builder $sub) use ($like): void {
                    $sub->where('title', 'like', $like)
                        ->orWhere('summary', 'like', $like)
                        ->orWhere('intro', 'like', $like);
                });
            })
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE, [
                'id', 'slug', 'title', 'youtube_id', 'thumbnail_url',
                'duration_seconds', 'published_at',
            ]);

        return view('livewire.video-search', [
            'videos' => $videos,
            'hasSearch' => $term !== '',
        ]);
    }
}
