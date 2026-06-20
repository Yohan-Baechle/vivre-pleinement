<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\LatestPosts;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\UpcomingAppointments;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
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
                'Rendez-vous',
                'Contenu',
                'Taxonomies',
                'Boutique',
                'SEO',
                'Site',
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                StatsOverview::class,
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
