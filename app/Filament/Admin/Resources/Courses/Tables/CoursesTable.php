<?php

namespace App\Filament\Admin\Resources\Courses\Tables;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateIcon(Heroicon::OutlinedAcademicCap)
            ->emptyStateHeading('Aucune formation')
            ->emptyStateDescription('Créez votre première formation : modules, '
                .'leçons et page de vente se règlent ensuite depuis sa fiche.')
            ->emptyStateActions([
                CreateAction::make()->label('Créer une formation'),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('')
                    ->collection('cover')
                    ->conversion('thumb')
                    ->height(48),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prix')
                    ->money('eur')
                    ->sortable(),
                TextColumn::make('enrollments_count')
                    ->label('Ventes')
                    ->counts(['enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)])
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publiée le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('–'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CourseStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
