<?php

namespace App\Services;

use App\Models\Page;

class SeoService
{
    public function isPrivate(?Page $page = null, bool $preview = false): bool
    {
        return ! app()->environment(['production', 'testing']) || $preview || config('starter.preview') || config('starter.exporting')
            || ($page && ($page->status !== 'published' || $page->noindex || $page->published_at?->isFuture()
                || in_array($page->path(), config('starter.retired_paths', []), true)
                || in_array($page->template, config('starter.retired_templates', []), true)));
    }

    public function url(string $path = '/'): string
    {
        return rtrim(config('starter.site_url'), '/').($path === '/' ? '/' : $path);
    }

    public function lastModified(Page $page): ?string
    {
        // Salon content also changes independently in catalog/profile tables.
        // Do not publish an incomplete page timestamp as a content-change signal.
        return str_starts_with($page->template, 'salon-') ? null : $page->updated_at?->toAtomString();
    }

    public function forPage(Page $page, bool $preview = false): array
    {
        $settings = app(SiteSettingsService::class);
        $private = $this->isPrivate($page, $preview);
        $canonical = $this->url($page->path());
        $media = $page->ogMedia?->status === 'ready' ? $page->ogMedia : null;
        $image = $media?->url();
        if ($image && (! parse_url($image, PHP_URL_HOST) || parse_url($image, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST))) {
            $image = $this->url('/'.ltrim(parse_url($image, PHP_URL_PATH), '/'));
        }
        $variant = collect($media?->variants ?? [])->sortByDesc('width')->first();
        $title = $page->seo_title ?: ($page->is_home ? $settings->get('default_title') : $page->title.' | '.$settings->get('site_name'));
        $description = $page->seo_description ?: $settings->get('default_description');
        $language = str_replace('_', '-', app()->getLocale());
        $business = ['@type' => 'Organization', '@id' => $this->url('/').'#business', 'name' => $settings->get('company_name'), 'url' => $this->url('/')];
        if (str_starts_with($page->template, 'salon-')) {
            $business = array_replace($business, app(SalonContent::class)->structuredData());
        }
        $socials = collect($settings->get('social_links', []))->reject(fn ($link) => $link['label'] === 'Google')->pluck('url')->values()->all();
        if ($socials) {
            $business['sameAs'] = $socials;
        }
        if ($image) {
            $business['image'] = $image;
        }
        $website = ['@type' => 'WebSite', '@id' => $this->url('/').'#website', 'url' => $this->url('/'), 'name' => $settings->get('site_name'), 'inLanguage' => $language, 'publisher' => ['@id' => $business['@id']]];
        $webpage = [
            '@type' => match ($page->template) {
                'salon-about' => 'AboutPage', 'salon-contact' => 'ContactPage', 'salon-gallery' => 'CollectionPage', default => 'WebPage',
            },
            '@id' => $canonical.'#webpage', 'url' => $canonical, 'name' => $title, 'description' => $description, 'inLanguage' => $language,
            'isPartOf' => ['@id' => $website['@id']], 'about' => ['@id' => $business['@id']],
        ];
        if ($image) {
            $webpage['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $image];
        }

        return [
            'title' => $title, 'site_name' => $settings->get('site_name'),
            'description' => $description,
            'canonical' => $canonical, 'robots' => $private ? 'noindex, nofollow' : 'index, follow',
            'image' => $image, 'image_alt' => $media?->alt, 'image_width' => $variant['width'] ?? null, 'image_height' => $variant['height'] ?? null,
            'locale' => str_starts_with(app()->getLocale(), 'vi') ? 'vi_VN' : 'en_US', 'language' => $language,
            'alternates' => $settings->get('features.multilingual') ? ($page->alternates ?? []) : [],
            'schema' => ['@context' => 'https://schema.org', '@graph' => [$business, $website, $webpage]],
        ];
    }
}
