<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Services\PageRenderer;
use App\Services\SeoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class SeoAudit extends Command
{
    protected $signature = 'starter:seo-audit';

    protected $description = 'Audit actual rendered public titles, descriptions, canonical, headings and image alt';

    public function handle(PageRenderer $renderer): int
    {
        View::share('errors', new ViewErrorBag);
        $titles = [];
        $descriptions = [];
        $issues = 0;
        foreach (Page::publiclyAccessible()->with(['heroMedia', 'ogMedia'])->lazyById(100) as $page) {
            try {
                $html = $renderer->render($page)->render();
            } catch (\Throwable $exception) {
                $this->error('FAIL '.$page->path().' render failed: '.$exception->getMessage());
                $issues++;

                continue;
            }
            $dom = new \DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new \DOMXPath($dom);
            $title = $dom->getElementsByTagName('title')->item(0)?->textContent;
            $description = $xpath->query('//meta[@name="description"]')->item(0)?->getAttribute('content');
            $errors = [];
            if ($dom->getElementsByTagName('title')->length !== 1 || ! $title || in_array($title, $titles)) {
                $errors[] = 'missing/duplicate title';
            }
            if (! $description || in_array($description, $descriptions)) {
                $errors[] = 'missing/duplicate description';
            }
            if ($xpath->query('//link[@rel="canonical"]')->length !== 1 || $xpath->query('//link[@rel="canonical"]')->item(0)?->getAttribute('href') !== app(SeoService::class)->url($page->path())) {
                $errors[] = 'canonical';
            }
            foreach (['description', 'robots', 'viewport', 'twitter:card', 'twitter:title', 'twitter:description'] as $name) {
                if ($xpath->query('//meta[@name="'.$name.'"]')->length !== 1) {
                    $errors[] = 'missing/duplicate '.$name;
                }
            }
            foreach (['og:title', 'og:description', 'og:url', 'og:type', 'og:locale', 'og:site_name'] as $property) {
                if ($xpath->query('//meta[@property="'.$property.'"]')->length !== 1) {
                    $errors[] = 'missing/duplicate '.$property;
                }
            }
            $expectedRobots = app(SeoService::class)->isPrivate($page) ? 'noindex, nofollow' : 'index, follow';
            if ($xpath->query('//meta[@name="robots"]')->item(0)?->getAttribute('content') !== $expectedRobots) {
                $errors[] = 'robots policy';
            }
            if ($dom->documentElement?->getAttribute('lang') !== str_replace('_', '-', app()->getLocale())) {
                $errors[] = 'document language';
            }
            $scripts = $xpath->query('//script[@type="application/ld+json"]');
            if ($scripts->length !== 1) {
                $errors[] = 'expected one JSON-LD graph';
            } else {
                try {
                    $schema = json_decode($scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
                    if (($schema['@context'] ?? null) !== 'https://schema.org' || ! count($schema['@graph'] ?? [])) {
                        $errors[] = 'invalid JSON-LD graph';
                    }
                } catch (\JsonException) {
                    $errors[] = 'invalid JSON-LD JSON';
                }
            }
            if ($dom->getElementsByTagName('h1')->length !== 1) {
                $errors[] = 'expected one h1';
            }
            if ($xpath->query('//img[not(@alt)]')->length) {
                $errors[] = 'missing alt';
            }
            $previousLevel = 0;
            foreach ($xpath->query('//main//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $heading) {
                $level = (int) substr($heading->nodeName, 1);
                if ($previousLevel && $level > $previousLevel + 1) {
                    $errors[] = 'skipped heading level';
                    break;
                }
                $previousLevel = $level;
            }
            $titles[] = $title;
            $descriptions[] = $description;
            $this->line(($errors ? 'FAIL ' : 'PASS ').$page->path().' '.implode(', ', $errors));
            $issues += count($errors);
        }
        $this->comment('Also inspect sitemap, robots, redirects and actual copy quality using docs/SEO.md.');

        return $issues ? self::FAILURE : self::SUCCESS;
    }
}
