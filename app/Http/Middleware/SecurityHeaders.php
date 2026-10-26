<?php

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production') && (PHP_VERSION_ID < 80512 || PHP_VERSION_ID >= 80600 || config('app.debug'))) {
            abort(503, 'Deployment configuration requires attention.');
        }
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $devSource = '';
        $devSocket = '';
        if (app()->environment('local') && is_file(public_path('hot'))) {
            $url = parse_url(trim(file_get_contents(public_path('hot'))));
            if (in_array($url['host'] ?? '', ['localhost', '127.0.0.1'], true) && in_array($url['scheme'] ?? '', ['http', 'https'], true)) {
                $host = $url['host'].(isset($url['port']) ? ':'.(int) $url['port'] : '');
                $devSource = ' '.$url['scheme'].'://'.$host;
                $devSocket = ' '.($url['scheme'] === 'https' ? 'wss' : 'ws').'://'.$host;
            }
        }
        $mapFrameSource = $request->is('contact-us') && app(SiteSettingsService::class)->get('maps_embed_url')
            ? ' https://www.google.com/maps/embed' : '';
        // Alpine/Livewire need evaluated expressions. Admin keeps the restricted policy.
        $policy = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'{$devSource} https://www.googletagmanager.com https://connect.facebook.net https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline'{$devSource}; img-src 'self' data: https:{$devSource}; font-src 'self' data:{$devSource}; connect-src 'self'{$devSource}{$devSocket} https://*.google-analytics.com https://www.googletagmanager.com https://www.facebook.com; frame-src https://challenges.cloudflare.com{$mapFrameSource}; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";
        $publicHtml = $response->isSuccessful()
            && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
            && ! $request->is('admin', 'admin/*', 'starter', 'starter/*', 'livewire', 'livewire/*', 'up')
            && ! config('starter.preview') && ! config('starter.exporting');
        $settings = app(SiteSettingsService::class);
        if ($publicHtml && (filled($settings->get('script_head')) || filled($settings->get('script_body_end')))) {
            // The site's administrator explicitly enables external embeds by supplying code.
            $policy = "default-src 'self' http: https: data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http: https: data: blob:; style-src 'self' 'unsafe-inline' http: https:; img-src 'self' http: https: data: blob:; font-src 'self' http: https: data:; connect-src 'self' http: https: ws: wss:; frame-src 'self' http: https: data: blob:; media-src 'self' http: https: data: blob:; worker-src 'self' blob:; object-src 'none'; base-uri 'self'; form-action 'self' http: https:; frame-ancestors 'self'";
        }
        $response->headers->set('Content-Security-Policy', $policy);
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }
        if ($request->is('admin', 'admin/*', 'livewire', 'livewire/*')) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }
        if (! app()->environment(['production', 'testing']) || config('starter.preview') || config('starter.exporting') || $response->isClientError() || $response->isServerError() || $request->is('admin', 'admin/*', 'starter', 'starter/*', 'livewire', 'livewire/*', 'up')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
