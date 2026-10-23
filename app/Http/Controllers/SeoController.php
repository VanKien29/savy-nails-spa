<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;

class SeoController extends Controller
{
    public function sitemap(SeoService $seo)
    {
        $pages = $seo->isPrivate() ? collect() : Page::publiclyAccessible()->where('noindex', false)->orderBy('id')->cursor();

        return response()->view('seo.sitemap', compact('pages', 'seo'))->header('Content-Type', 'application/xml');
    }

    public function robots(SeoService $seo)
    {
        $content = $seo->isPrivate() ? "User-agent: *\nDisallow: /\n" : "User-agent: *\nDisallow: /admin/\nDisallow: /starter/\nDisallow: /livewire/\nSitemap: ".$seo->url('/sitemap.xml')."\n";

        return response($content)->header('Content-Type', 'text/plain');
    }
}
