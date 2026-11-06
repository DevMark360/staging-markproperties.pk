<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
            ->default()
            ->id('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->path(config('admin.path'))
            ->login()
            ->profile(isSimple: false)
            ->brandName('Mark Properties')
            // The original logo (login page; the sidebar shows it too, in sidebar-brand.blade.php)
            ->brandLogo(asset('images/logo-dark.png'))
            ->darkModeBrandLogo(asset('images/logo-light.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Red, // Tailwind red-600 (#dc2626) — closest solid match to the brand red #ed1c24
                'gray' => Color::Neutral,
            ])
            ->font('Inter')
            ->navigationGroups(['Leads', 'Content', 'Lists'])
            // Workspace-style layout: no top bar; a full-height 260px sidebar with the logo (its menu holds My profile),
            // a Search row, the links, and View website / Log out at the foot. Every page has a show/hide menu button
            // beside its breadcrumbs. Search opens a pop-up (also Ctrl/⌘ K) instead of Filament's search box.
            ->topbar(false)
            ->userMenu(false)
            ->sidebarWidth('16.25rem')
            ->sidebarFullyCollapsibleOnDesktop()
            ->globalSearch(false)
            ->renderHook(PanelsRenderHook::SIDEBAR_START, fn () => view('filament.admin.sidebar-brand'))
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn () => view('filament.admin.sidebar-search'))
            ->renderHook(PanelsRenderHook::SIDEBAR_FOOTER, fn () => view('filament.admin.sidebar-footer'))
            ->renderHook(PanelsRenderHook::PAGE_START, fn () => view('filament.admin.page-toggle'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('filament.admin.search-modal'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
