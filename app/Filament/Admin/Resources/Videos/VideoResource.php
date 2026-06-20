<?php

namespace App\Filament\Admin\Resources\Videos;

use App\Filament\Admin\Resources\Videos\Pages\EditVideo;
use App\Filament\Admin\Resources\Videos\Pages\ListVideos;
use App\Filament\Admin\Resources\Videos\Schemas\VideoForm;
use App\Filament\Admin\Resources\Videos\Tables\VideosTable;
use App\Models\Video;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $navigationLabel = 'Vidéos';

    protected static ?string $modelLabel = 'Vidéo';

    protected static ?string $pluralModelLabel = 'Vidéos';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Contenu';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $missing = Video::query()->where('is_missing', true)->count();

        return $missing > 0 ? (string) $missing : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Vidéos disparues de YouTube (à traiter)';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'youtube_id', 'slug'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Statut' => $record->is_missing
                ? '⚠️ Manquante sur YouTube'
                : $record->status->getLabel(),
            'Publiée le' => $record->published_at?->format('d/m/Y') ?? '–',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return VideoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VideosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVideos::route('/'),
            'edit' => EditVideo::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
