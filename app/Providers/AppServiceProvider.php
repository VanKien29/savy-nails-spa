<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\SiteSetting;
use App\Models\User;
use App\Observers\ContentObserver;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('salon.pages.*', function ($view) {
            $view->with('salon', app(SalonContent::class)->all());
        });
        foreach ([Page::class, SiteSetting::class, NavigationItem::class, Media::class, Redirect::class, Lead::class, User::class] as $model) {
            $model::observe(ContentObserver::class);
        }
        RateLimiter::for('leads', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
