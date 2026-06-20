<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestPosts extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Derniers articles';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Post::query()->latest('published_at')->limit(5))
            ->paginated(false)
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')
                    ->collection('featured')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(70)
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Modifier')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => route('filament.admin.resources.posts.edit', $record)),
            ]);
    }
}
