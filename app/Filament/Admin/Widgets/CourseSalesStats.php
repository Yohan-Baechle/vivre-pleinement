<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CourseSalesStats extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Formations';

    protected function getStats(): array
    {
        $activeSales = Enrollment::query()->where('status', EnrollmentStatus::Active);

        $revenueCents = (int) (clone $activeSales)->sum('amount_paid_cents');
        $salesLast30Days = (clone $activeSales)->where('purchased_at', '>=', now()->subDays(30))->count();

        $bestSeller = Course::query()
            ->withCount(['enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)])
            ->orderByDesc('enrollments_count')
            ->first();

        return [
            Stat::make('Chiffre d\'affaires formations', number_format($revenueCents / 100, 2, ',', ' ').' €')
                ->description('Ventes confirmées')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(route('filament.admin.resources.enrollments.index')),

            Stat::make('Ventes (30 derniers jours)', $salesLast30Days)
                ->description('Nouvelles inscriptions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Formation la plus vendue', $bestSeller?->enrollments_count ? $bestSeller->title : '–')
                ->description($bestSeller?->enrollments_count ? $bestSeller->enrollments_count.' vente(s)' : 'Aucune vente')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
        ];
    }
}
