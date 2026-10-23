<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use App\Services\PageRenderer;
use App\Services\SeoService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, PageRenderer $renderer, string $slug = '')
    {
        $slug = trim($slug, '/');
        $path = '/'.$slug;
        $visited = [];
        while ($redirect = Redirect::where('source', $path)->first()) {
            abort_if(in_array($path, $visited, true) || count($visited) >= 20, 404);
            $visited[] = $path;
            $path = $redirect->target;
        }
        $pageSlug = trim($path, '/');
        $page = Page::publiclyAccessible()->when($path === '/', fn ($q) => $q->where('is_home', true), fn ($q) => $q->where('slug', $pageSlug)->where('is_home', false))->firstOrFail();
        $seo = app(SeoService::class);
        $canonical = $seo->url($page->path());
        $origin = parse_url($canonical);
        $port = $origin['port'] ?? (($origin['scheme'] ?? '') === 'https' ? 443 : 80);
        $wrongOrigin = app()->environment('production') && ($request->getScheme() !== ($origin['scheme'] ?? '')
            || strcasecmp($request->getHost(), $origin['host'] ?? '') !== 0 || $request->getPort() !== $port);
        if ($visited || $request->getPathInfo() !== $page->path() || $wrongOrigin) {
            $destination = ($wrongOrigin ? $canonical : $page->path()).($request->getQueryString() ? '?'.$request->getQueryString() : '');

            return redirect()->to($destination, 301);
        }

        $response = response($renderer->render($page)->render())->header('Cache-Control', 'private, no-store');

        return $seo->isPrivate($page) ? $response->header('X-Robots-Tag', 'noindex, nofollow') : $response;
    }

    public function preview(Page $page, PageRenderer $renderer)
    {
        abort_unless(auth()->user()?->is_admin, 403);

        return response($renderer->render($page, true)->render())->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
    }
}
