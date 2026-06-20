<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Vue d\'ensemble';

    protected function getStats(): array
    {
        $publishedPosts = Post::where('status', PostStatus::Published)->count();
        $draftPosts = Post::where('status', PostStatus::Draft)->count();
        $pendingComments = Comment::where('status', CommentStatus::Pending)->count();
        $activeProducts = Product::where('is_active', true)->count();

        return [
            Stat::make('Articles publiés', $publishedPosts)
                ->description($draftPosts.' brouillon(s)')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->url(route('filament.admin.resources.posts.index')),

            Stat::make('Catégories', Category::count())
                ->description('Organisez vos articles')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info')
                ->url(route('filament.admin.resources.categories.index')),

            Stat::make('Commentaires en attente', $pendingComments)
                ->description($pendingComments > 0 ? 'À modérer' : 'Aucun à modérer')
                ->descriptionIcon($pendingComments > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingComments > 0 ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.comments.index')),

            Stat::make('Produits actifs', $activeProducts)
                ->description('En vente')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->url(route('filament.admin.resources.products.index')),
        ];
    }
}
