<?php

namespace App\Providers\Filament;

use App\Filament\Pages;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources;
use App\Http\Middleware\AdminLocale;
use App\Models\Media;
use App\Services\CmsAccess;
use App\Services\SiteSettingsService;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
        if (! in_array(config('mail.default'), ['log', 'array'], true)) {
            $panel->passwordReset();
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn () => app(SiteSettingsService::class)->get('site_name'))
            ->brandLogo(fn () => Media::find(app(SiteSettingsService::class)->get('logo_id'))?->url())
            ->brandLogoHeight('3rem')
            ->login()
            ->profile()
            ->userMenuItems(['profile' => fn (Action $action) => $action->hidden()])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(asset('assets/savy/favicon.svg'))
            ->font('Be Vietnam Pro', provider: LocalFontProvider::class)
            ->darkMode(false)
            ->sidebarWidth('16rem')
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->strictAuthorization()
            ->colors(fn () => ['primary' => Color::hex(app(SiteSettingsService::class)->get('primary_color', '#ae8346'))])
            ->navigation(function (NavigationBuilder $builder) {
                $definitions = [
                    ['Tổng quan', 'heroicon-o-squares-2x2', Dashboard::getUrl(), 'filament.admin.pages.dashboard', null],
                    ['Nội dung website', 'heroicon-o-pencil-square', Pages\WebsiteContent::getUrl(), 'filament.admin.pages.website-content', 'content'],
                    ['Header kéo thả', 'heroicon-o-bars-3', Pages\HeaderMenu::getUrl(), 'filament.admin.pages.header-menu', 'content'],
                    ['Cài đặt Footer', 'heroicon-o-window', Pages\FooterSettings::getUrl(), 'filament.admin.pages.footer-settings', 'settings'],
                    ['Menu dịch vụ', 'heroicon-o-sparkles', Pages\ServiceMenu::getUrl(), 'filament.admin.pages.service-menu', 'content'],
                    ['Thư viện ảnh', 'heroicon-o-photo', Pages\MediaLibrary::getUrl(), 'filament.admin.pages.media-library', 'content'],
                    ['SEO', 'heroicon-o-magnifying-glass', Pages\SeoSettings::getUrl(), 'filament.admin.pages.seo-settings', 'settings'],
                    ['Script / Widget', 'heroicon-o-code-bracket', Pages\ScriptWidgets::getUrl(), 'filament.admin.pages.script-widgets', 'settings'],
                    ['Tài khoản Admin', 'heroicon-o-shield-check', Resources\UserResource::getUrl(), 'filament.admin.resources.users.*', 'users'],
                    ['Nhật ký hoạt động', 'heroicon-o-clock', Resources\AuditLogResource::getUrl(), 'filament.admin.resources.audit-logs.*', 'audit'],
                ];
                $items = [];
                foreach ($definitions as [$label, $icon, $url, $match, $permission]) {
                    if ($permission !== null && ! CmsAccess::allows($permission)) {
                        continue;
                    }
                    $items[] = NavigationItem::make($label)->icon($icon)->url($url)->isActiveWhen(fn () => request()->routeIs($match));
                }

                return $builder->groups([NavigationGroup::make('Quản trị website')->items($items)->collapsible(false)]);
            })
            ->renderHook('panels::sidebar.nav.end', fn () => view('filament.partials.sidebar-footer'))
            ->renderHook('panels::topbar.logo.after', fn () => view('filament.partials.topbar-title'))
            ->renderHook('panels::global-search.before', fn () => view('filament.partials.topbar-user'))
            ->resources([
                Resources\UserResource::class,
                Resources\AuditLogResource::class,
            ])
            ->pages([
                Dashboard::class,
                Pages\WebsiteContent::class,
                Pages\ServiceMenu::class,
                Pages\HeaderMenu::class,
                Pages\FooterSettings::class,
                Pages\MediaLibrary::class,
                Pages\SeoSettings::class,
                Pages\ScriptWidgets::class,
                Pages\SiteSettings::class,
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
            ->middleware([AdminLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
