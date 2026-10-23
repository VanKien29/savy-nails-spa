<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use App\Services\SeoService;
use App\Services\SiteSettingsService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        config(['starter.site_url' => 'https://production.example', 'starter.preview' => false]);
    }

    public function test_sitemap_excludes_retired_and_redirected_pages_and_preserves_lastmod(): void
    {
        Page::create(['title' => 'Retired', 'slug' => 'booking', 'status' => 'published', 'template' => 'salon-booking']);
        Page::create(['title' => 'Moved', 'slug' => 'moved', 'status' => 'published', 'template' => 'sections']);
        Redirect::create(['source' => '/moved', 'target' => '/dich-vu/thiet-ke']);
        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(2, $xml->url);
        $this->assertSame('https://production.example/', (string) $xml->url[0]->loc);
        $this->assertNotEmpty((string) $xml->url[0]->lastmod);
        $response->assertDontSee('/booking')->assertDontSee('/moved');
        $this->get('/booking/')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_query_parameters_keep_clean_canonical_and_slash_redirect_preserves_filters(): void
    {
        $this->get('/dich-vu/thiet-ke?category=kids&utm_source=test')->assertOk()
            ->assertSee('<link rel="canonical" href="https://production.example/dich-vu/thiet-ke">', false);
        // Relative test URLs pass through Laravel's URL generator, which trims slashes.
        $this->get('http://localhost/dich-vu/thiet-ke/?category=kids')->assertStatus(301)->assertRedirect('/dich-vu/thiet-ke?category=kids');
        $this->get('/not-found')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_manual_redirect_chains_resolve_in_one_hop_to_a_live_page(): void
    {
        Redirect::create(['source' => '/oldest', 'target' => '/older']);
        Redirect::create(['source' => '/older', 'target' => '/dich-vu/thiet-ke']);
        $this->get('/oldest/?utm_source=test')->assertStatus(301)->assertRedirect('/dich-vu/thiet-ke?utm_source=test');
        Redirect::create(['source' => '/dead', 'target' => '/missing']);
        $this->get('/dead')->assertNotFound();
    }

    public function test_production_origin_redirect_does_not_touch_admin_routes(): void
    {
        app()->instance('env', 'production');
        config(['app.debug' => false]);
        $this->get('http://www.production.example/dich-vu/thiet-ke/?category=kids')->assertStatus(301)
            ->assertRedirect('https://production.example/dich-vu/thiet-ke?category=kids');
        $this->get('https://production.example/dich-vu/thiet-ke')->assertOk()->assertDontSee('noindex, nofollow');
        $this->get('https://production.example/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        config(['starter.site_url' => 'https://production.example:443']);
        $this->get('https://production.example/dich-vu/thiet-ke')->assertOk();
    }

    public function test_metadata_fallback_uses_absolute_og_image_and_json_cannot_close_script(): void
    {
        Storage::fake('public');
        $media = Media::create(['name' => 'Share', 'alt' => 'An accurate image description', 'mime' => 'image/webp', 'disk' => 'public', 'original_path' => 'originals/share.webp', 'status' => 'ready', 'width' => 1200, 'height' => 630, 'size' => 1000, 'variants' => [['path' => 'media/1/1200.webp', 'width' => 1200, 'height' => 630]]]);
        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace($settings->all(), ['og_media_id' => $media->id]));
        $home = Page::where('is_home', true)->firstOrFail();
        $home->update(['seo_title' => '</script><script>alert("title")</script>', 'seo_description' => 'A "quoted" & safely escaped description']);
        $html = $this->get('/')->assertOk()->assertDontSee('<script>alert("title")</script>', false)->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame('https://production.example/storage/media/1/1200.webp', $xpath->query('//meta[@property="og:image"]')->item(0)->getAttribute('content'));
        $this->assertSame('1200', $xpath->query('//meta[@property="og:image:width"]')->item(0)->getAttribute('content'));
        $this->assertSame($media->alt, $xpath->query('//meta[@name="twitter:image:alt"]')->item(0)->getAttribute('content'));
        $schema = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($home->seo_title, $schema['@graph'][2]['name']);
        $this->assertSame('https://production.example/#website', $schema['@graph'][2]['isPartOf']['@id']);
    }

    public function test_exporting_local_and_scheduled_previews_share_indexing_policy(): void
    {
        config(['starter.exporting' => true]);
        $this->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('noindex, nofollow');
        $this->get('/sitemap.xml')->assertDontSee('<loc>', false);
        $this->get('/robots.txt')->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap:');
        config(['starter.exporting' => false]);
        app()->instance('env', 'local');
        $this->get('/')->assertOk()->assertSee('noindex, nofollow');
        $this->get('/sitemap.xml')->assertDontSee('<loc>', false);
        $scheduled = Page::where('is_home', true)->firstOrFail();
        $scheduled->published_at = now()->addDay();
        $this->assertTrue(app(SeoService::class)->isPrivate($scheduled));
    }

    public function test_audit_reports_a_missing_active_view_without_crashing(): void
    {
        config(['starter.templates.missing-view' => 'pages.does-not-exist']);
        Page::create(['title' => 'Broken view', 'slug' => 'broken-view', 'status' => 'published', 'template' => 'missing-view']);
        $this->artisan('starter:seo-audit')->expectsOutputToContain('FAIL /broken-view render failed:')->assertFailed();
    }
}
