<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: app_path('Modules/BuckEYE/Filament/Resources'), for: 'App\\Modules\\BuckEYE\\Filament\\Resources')
            ->discoverResources(in: app_path('Modules/OhioWordle/Filament/Resources'), for: 'App\\Modules\\OhioWordle\\Filament\\Resources')
            ->discoverResources(in: app_path('Modules/SpamProtection/Filament/Resources'), for: 'App\\Modules\\SpamProtection\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverWidgets(in: app_path('Modules/BuckEYE/Filament/Widgets'), for: 'App\\Modules\\BuckEYE\\Filament\\Widgets')
            ->discoverWidgets(in: app_path('Modules/OhioWordle/Filament/Widgets'), for: 'App\\Modules\\OhioWordle\\Filament\\Widgets')
            ->discoverWidgets(in: app_path('Modules/SpamProtection/Filament/Widgets'), for: 'App\\Modules\\SpamProtection\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                \App\Modules\BuckEYE\Filament\Widgets\TodaysPuzzleStatsWidget::class,
                \App\Modules\BuckEYE\Filament\Widgets\TodaysPuzzlePlayersWidget::class,
                \App\Modules\OhioWordle\Filament\Widgets\TodaysWordleStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
