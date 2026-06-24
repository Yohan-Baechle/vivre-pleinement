<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Vue d'ensemble de l'état éditorial des vidéos, affichée en tête de la liste.
 */
class VideoStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'État des vidéos';

    protected function getStats(): array
    {
        $base = Video::query()->published();

        $total = (clone $base)->count();
        $enriched = (clone $base)
            ->whereNotNull('intro')->where('intro', '!=', '')
            ->whereNotNull('summary')->where('summary', '!=', '')
            ->count();
        $withTranscript = (clone $base)
            ->whereNotNull('transcript')->where('transcript', '!=', '')
            ->count();
        $totalViews = (int) (clone $base)->sum('view_count');

        $pct = fn (int $n) => $total > 0 ? (int) round($n / $total * 100) : 0;
        $enrichedPct = $pct($enriched);
        $transcriptPct = $pct($withTranscript);

        return [
            Stat::make('Vidéos publiées', $total)
                ->description('Vidéos longues en ligne')
                ->descriptionIcon('heroicon-m-film')
                ->color('primary'),

            Stat::make('Enrichies', $enriched.' / '.$total)
                ->description($enrichedPct.'% avec intro + résumé')
                ->descriptionIcon($enrichedPct >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-pencil-square')
                ->color($enrichedPct >= 100 ? 'success' : 'warning'),

            Stat::make('Avec transcription', $withTranscript.' / '.$total)
                ->description($transcriptPct.'% transcrites')
                ->descriptionIcon($transcriptPct >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-document-text')
                ->color(match (true) {
                    $transcriptPct >= 100 => 'success',
                    $transcriptPct > 0 => 'warning',
                    default => 'danger',
                }),

            Stat::make('Vues cumulées', number_format($totalViews, 0, ',', ' '))
                ->description('Toutes vidéos confondues')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
        ];
    }
}
