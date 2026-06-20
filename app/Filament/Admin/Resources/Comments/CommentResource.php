<?php

namespace App\Filament\Admin\Resources\Comments;

use App\Enums\CommentStatus;
use App\Filament\Admin\Resources\Comments\Pages\CreateComment;
use App\Filament\Admin\Resources\Comments\Pages\EditComment;
use App\Filament\Admin\Resources\Comments\Pages\ListComments;
use App\Filament\Admin\Resources\Comments\Schemas\CommentForm;
use App\Filament\Admin\Resources\Comments\Tables\CommentsTable;
use App\Models\Comment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Commentaires';

    protected static ?string $modelLabel = 'Commentaire';

    protected static ?string $pluralModelLabel = 'Commentaires';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Contenu';

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', CommentStatus::Pending)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
            'create' => CreateComment::route('/create'),
            'edit' => EditComment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
