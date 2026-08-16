<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\CourseSalesStats;
use App\Filament\Admin\Widgets\LatestPosts;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\UpcomingAppointments;
use App\Filament\AvatarProviders\InitialsAvatarProvider;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('espace-pro')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->profile()
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->brandName('Vivre Pleinement')
                    ->recoverable(),
            ], isRequired: true)
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->brandName('Vivre Pleinement')
            ->brandLogo(asset('images/logo@2x.webp'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => '#117d89',
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make('Rendez-vous')
                    ->icon(Heroicon::OutlinedCalendarDays),
                NavigationGroup::make('Contenu')
                    ->icon(Heroicon::OutlinedPencilSquare),
                NavigationGroup::make('Boutique')
                    ->icon(Heroicon::OutlinedShoppingBag),
                NavigationGroup::make('Réglages du site')
                    ->icon(Heroicon::OutlinedCog6Tooth),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                StatsOverview::class,
                CourseSalesStats::class,
                UpcomingAppointments::class,
                LatestPosts::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
