<?php

namespace App\Services;

use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageRenderer
{
    public function render(Page $page, bool $preview = false): View
    {
        $settings = app(SiteSettingsService::class)->all();
        $ids = collect($page->sections ?? [])->pluck('media_id')->merge(collect($page->sections ?? [])->pluck('items')->flatten(1)->pluck('media_id'))
            ->merge(collect($settings)->only(['logo_id', 'mobile_logo_id', 'favicon_id', 'og_media_id'])->values())->filter()->unique();
        $media = Media::whereIn('id', $ids)->where('status', 'ready')->get()->keyBy('id');
        $page->loadMissing(['heroMedia', 'ogMedia']);
        if (! $page->ogMedia && ($id = $settings['og_media_id'])) {
            $page->setRelation('ogMedia', $media->get($id));
        }

        return view(config('starter.templates.'.$page->template), [
            'page' => $page, 'settings' => $settings, 'media' => $media,
            'sections' => collect($page->sections ?? [])->filter(fn ($s) => ($s['enabled'] ?? false)
                && ($page->template !== 'salon-home' || ($s['id'] ?? '') !== 'questions'))->sortBy('ordering'),
            'navigation' => Cache::remember('starter.navigation', 600, fn () => NavigationItem::where('enabled', true)->orderBy('sort_order')->get()),
            'seo' => app(SeoService::class)->forPage($page, $preview),
            'isDemo' => config('starter.preview') || config('starter.exporting'),
            'isPreview' => $preview,
        ]);
    }
}
