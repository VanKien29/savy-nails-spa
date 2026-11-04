<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SalonProfile;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyMapLinkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavyMapEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_savy_pages_embed_the_configured_google_map_with_scoped_frame_permissions(): void
    {
        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace(SiteSettingsService::defaults(), [
            'site_name' => 'Savy Nails & Spa',
            'maps_url' => SavyMapLinkSeeder::MAP_URL,
            'maps_embed_url' => SavyMapLinkSeeder::EMBED_URL,
        ]));
        SalonProfile::create(['id' => 1, 'data' => ['hours' => []]]);
        foreach ([
            ['home', 'salon-savy-home'],
            ['about-us', 'salon-about'],
            ['services', 'salon-services'],
            ['gallery', 'salon-gallery'],
            ['contact-us', 'salon-contact'],
        ] as [$slug, $template]) {
            Page::create(['title' => $slug, 'slug' => $slug, 'template' => $template, 'is_home' => $slug === 'home', 'status' => 'published', 'sections' => []]);
            $response = $this->get($slug === 'home' ? '/' : '/'.$slug)->assertOk();
            $response->assertSee('data-auto-map="'.e(SavyMapLinkSeeder::EMBED_URL).'"', false)
                ->assertSee('<noscript>', false)
                ->assertSee('src="'.e(SavyMapLinkSeeder::EMBED_URL).'"', false)
                ->assertSee('loading="lazy"', false)
                ->assertDontSee('View interactive map');
            $this->assertStringContainsString('https://www.google.com/maps/embed', $response->headers->get('Content-Security-Policy'));
        }
        $this->assertStringNotContainsString('https://www.google.com/maps/embed', $this->get('/admin/login')->headers->get('Content-Security-Policy'));
        $settings->update(array_replace($settings->all(), ['maps_embed_url' => '']));
        $response = $this->get('/contact-us')->assertOk()->assertDontSee('<iframe', false);
        $this->assertStringNotContainsString('https://www.google.com/maps/embed', $response->headers->get('Content-Security-Policy'));
    }
}
