<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\SiteSetting;
use App\Models\User;
use App\Observers\ContentObserver;
use App\Policies\AuditLogPolicy;
use App\Policies\CmsPolicy;
use App\Policies\LeadPolicy;
use App\Policies\SalonProfilePolicy;
use App\Policies\SiteSettingPolicy;
use App\Policies\UserPolicy;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SiteSettingsService::class);
        $this->app->scoped(SalonContent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(14)->mixedCase()->numbers()->symbols());
        View::composer('salon.pages.*', function ($view) {
            $salon = app(SalonContent::class)->all();
            $view->with('salon', $salon)->with('copy', $salon['profile']['copy']);
        });
        foreach ([Page::class, SiteSetting::class, NavigationItem::class, Media::class, Redirect::class, Lead::class, User::class, SalonProfile::class, SalonImage::class, SalonService::class, SalonCategory::class] as $model) {
            $policy = match ($model) {
                User::class => UserPolicy::class,
                Lead::class => LeadPolicy::class,
                AuditLog::class => AuditLogPolicy::class,
                SiteSetting::class => SiteSettingPolicy::class,
                SalonProfile::class => SalonProfilePolicy::class,
                default => CmsPolicy::class,
            };
            Gate::policy($model, $policy);
            $model::observe(ContentObserver::class);
        }
        RateLimiter::for('leads', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
