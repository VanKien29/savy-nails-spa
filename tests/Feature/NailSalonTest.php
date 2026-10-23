<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Page;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use App\Services\StaticPreviewExporter;
use Database\Seeders\NailSalonSeeder;
use Database\Seeders\SalonBrandSeeder;
use Database\Seeders\SalonHeroSlideshowSeeder;
use Database\Seeders\SalonSeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NailSalonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Queue::fake();
        Cache::flush();
        config(['app.locale' => 'en', 'starter.preview' => false, 'starter.site_url' => 'https://salon.example']);
        app()->setLocale('en');
        $this->seed(NailSalonSeeder::class);
    }

    private function payload(array $extra = []): array
    {
        return ['full_name' => 'Test Guest', 'phone' => '9105550123', 'email' => 'guest@example.invalid', 'page_id' => Page::where('slug', 'booking')->value('id'), 'form_token' => Crypt::encryptString((string) (time() - 5)), 'consent' => '1', 'extra' => array_replace(['form' => 'booking', 'service_id' => SalonService::first()->id, 'date' => now('America/New_York')->addDay()->toDateString(), 'time' => 'afternoon'], $extra)];
    }

    public function test_five_english_pages_and_source_menu_render(): void
    {
        $this->assertSame(58, SalonService::count());
        foreach (['/', '/about-us', '/services', '/gallery', '/contact-us'] as $path) {
            $response = $this->get($path)->assertOk()->assertSee('<html lang="en">', false)->assertSee('THE K')->assertSee('09:30 AM');
            $dom = new \DOMDocument;
            @$dom->loadHTML($response->getContent());
            $this->assertCount(1, $dom->getElementsByTagName('h1'));
        }
        $this->get('/services')->assertSee('Golden 99K Fairy Queen')->assertSee('$99')->assertSee('$114')->assertSee('50% of Full Set Price');
        $this->get('/contact-us')->assertSee('07:00 PM')->assertSee('facebook.com/profile.php', false)->assertSee('instagram.com/thek.luxurynailbar', false);
        $this->get('/booking')->assertNotFound();
        $this->get('/missing-salon-page')->assertNotFound();
    }

    public function test_admin_logo_selection_updates_branding_and_is_preserved_by_seed(): void
    {
        $settings = app(SiteSettingsService::class);
        $original = Media::findOrFail($settings->get('logo_id'));
        $this->assertSame($original->id, $settings->get('mobile_logo_id'));
        $this->assertTrue(app(MediaService::class)->inUse($original));
        $desktop = app(MediaService::class)->upload(UploadedFile::fake()->image('desktop.png', 240, 80), 'Desktop logo');
        $mobile = app(MediaService::class)->upload(UploadedFile::fake()->image('mobile.png', 180, 180), 'Mobile logo');
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin);
        Livewire::test(SiteSettings::class)->fillForm(['logo_id' => $desktop->id, 'mobile_logo_id' => $mobile->id])->call('save')->assertHasNoFormErrors();
        $dom = new \DOMDocument;
        @$dom->loadHTML($this->get('/')->assertOk()->getContent());
        $xpath = new \DOMXPath($dom);
        $header = $xpath->query('//header//picture[contains(@class,"ks-brand-logo")]//img');
        $footer = $xpath->query('//footer//picture[contains(@class,"ks-brand-logo")]//img');
        $this->assertCount(1, $header);
        $this->assertCount(1, $footer);
        foreach ([$header->item(0), $footer->item(0)] as $image) {
            $this->assertStringContainsString('/media/'.$desktop->id.'/', $image->getAttribute('src'));
            $this->assertSame('THE K LUXURY NAIL BAR', $image->getAttribute('alt'));
        }
        $this->assertSame('eager', $header->item(0)->getAttribute('loading'));
        $this->assertSame('lazy', $footer->item(0)->getAttribute('loading'));
        $mobileSource = $xpath->query('//header//picture[contains(@class,"ks-brand-logo")]//source[@media]')->item(0);
        $this->assertStringContainsString('/media/'.$mobile->id.'/', $mobileSource->getAttribute('srcset'));
        $this->assertSame('(max-width: 730px)', $mobileSource->getAttribute('media'));
        $this->seed(SalonBrandSeeder::class);
        $this->assertSame($desktop->id, $settings->get('logo_id'));
        $this->assertSame($mobile->id, $settings->get('mobile_logo_id'));
    }

    public function test_home_hero_slides_are_profile_driven_and_seed_preserves_editor_choice(): void
    {
        $profile = SalonProfile::findOrFail(1);
        $this->assertSame(['hero-marble-editorial', 'hero-gold-nail-oil', 'hero-manicure-ritual'], $profile->data['hero_slides']);
        $data = $profile->data;
        $data['hero_slides'] = ['hero-gold-nail-oil'];
        $profile->update(['data' => $data]);
        $this->seed(SalonHeroSlideshowSeeder::class);
        $this->assertSame(['hero-gold-nail-oil'], SalonProfile::findOrFail(1)->data['hero_slides']);
        $this->get('/')->assertSee('data-hero-slide="0"', false)->assertDontSee('data-hero-slide="1"', false);
    }

    public function test_salon_seo_graph_uses_verified_address_hours_and_keeps_retired_booking_out(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk()->assertDontSee('/booking')->getContent();
        $this->assertCount(5, simplexml_load_string($sitemap)->url);
        $this->assertStringNotContainsString('<lastmod>', $sitemap);
        $dom = new \DOMDocument;
        @$dom->loadHTML($this->get('/')->assertOk()->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(1, $xpath->query('//script[@type="application/ld+json"]'));
        $schema = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        $business = $schema['@graph'][0];
        $this->assertSame('NailSalon', $business['@type']);
        $this->assertSame('THE K LUXURY NAIL BAR', $business['name']);
        $this->assertSame('28314', $business['address']['postalCode']);
        $this->assertSame('+19104910358', $business['telephone']);
        $this->assertSame('09:30', $business['openingHoursSpecification'][0]['opens']);
        $this->assertSame('19:30', $business['openingHoursSpecification'][0]['closes']);
        $this->assertSame('19:00', $business['openingHoursSpecification'][1]['closes']);
        $this->assertCount(6, $business['openingHoursSpecification'][0]['dayOfWeek']);
        $this->assertArrayNotHasKey('aggregateRating', $business);
        $this->artisan('starter:seo-audit')->assertSuccessful();
        $home = Page::where('is_home', true)->firstOrFail();
        $home->update(['seo_title' => 'An owner-edited title']);
        $profile = SalonProfile::findOrFail(1);
        $data = $profile->data;
        $data['hours'][0]['time'] = '10:00 AM – 07:30 PM';
        $profile->update(['data' => $data]);
        $this->seed(SalonSeoSeeder::class);
        $this->assertSame('An owner-edited title', $home->fresh()->seo_title);
        app()->forgetScopedInstances();
        $this->assertSame('10:00', app(SalonContent::class)->structuredData()['openingHoursSpecification'][0]['opens']);
    }

    public function test_booking_persists_preferences_and_deduplicates_same_request(): void
    {
        $payload = $this->payload();
        $payload['notes'] = 'Please add gel polish.';
        $this->postJson('/inquiries/booking', $payload)->assertCreated()->assertJsonPath('message', fn ($message) => str_contains($message, 'not confirmed'));
        $lead = Lead::firstOrFail();
        $this->assertSame('new', $lead->status);
        $this->assertSame('/booking', $lead->source_page);
        $this->assertSame($payload['extra'], $lead->extra);
        $this->assertStringContainsString('Please add gel polish.', $lead->message);
        $this->postJson('/inquiries/booking', $payload)->assertCreated();
        $this->assertSame(1, Lead::count());
        $payload['extra']['date'] = now('America/New_York')->addDays(2)->toDateString();
        $this->postJson('/inquiries/booking', $payload)->assertCreated();
        $this->assertSame(2, Lead::count());
    }

    public function test_editorial_inspiration_is_labelled_and_seed_preserves_edits(): void
    {
        $images = SalonImage::where('category', 'nails')->where('show_in_gallery', true)->get();
        $this->assertCount(6, $images);
        foreach ($images as $image) {
            if ($image->source_url) {
                $this->assertStringStartsWith('https://', $image->source_url);
                $this->assertStringContainsString('Pinterest', $image->source_credit);
            } else {
                $this->assertSame('AI-created editorial inspiration', $image->source_credit);
            }
            $this->assertTrue($image->is_inspiration);
        }
        $image = $images->first();
        $image->update(['caption' => 'Edited inspiration caption']);
        $this->seed(NailSalonSeeder::class);
        $this->assertSame('Edited inspiration caption', $image->fresh()->caption);
        $this->get('/gallery')->assertSee('AI-created')->assertSee('Edited inspiration caption');
    }

    public function test_photographs_are_unique_across_all_public_pages_and_home_has_eight_sections(): void
    {
        $seen = [];
        foreach (['/', '/about-us', '/services', '/gallery', '/contact-us'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match_all('/data-salon-image="([a-z0-9-]+)"/', $html, $matches);
            foreach ($matches[1] as $key) {
                $this->assertArrayNotHasKey($key, $seen, $key.' repeated on '.$path);
                $seen[$key] = $path;
            }
        }
        $home = Page::where('template', 'salon-home')->firstOrFail();
        $this->assertCount(8, $home->sections);
        $this->get('/')->assertDontSee('Your neighborhood luxury')->assertDontSee('Beyond the polish')->assertDontSee('A considered detail.')->assertDontSee('A little moment. A beautiful difference.')->assertSee('Before your beautiful moment.')->assertSee('A slower kind of luxury')->assertSee('Thoughtful from start to finish')->assertSee('sizes="(max-width:960px) 100vw, 65vw"', false);
    }

    public function test_booking_rejects_invalid_service_date_and_untrusted_extra(): void
    {
        $this->postJson('/inquiries/booking', $this->payload(['service_id' => 999999, 'date' => '2020-01-01', 'price' => 1]))->assertUnprocessable()->assertJsonValidationErrors(['extra', 'extra.service_id', 'extra.date']);
        $service = SalonService::first();
        $service->update(['active' => false]);
        $this->postJson('/inquiries/booking', $this->payload(['service_id' => $service->id]))->assertUnprocessable()->assertJsonValidationErrors('extra.service_id');
        $this->assertSame(0, Lead::count());
    }

    public function test_contact_persists_and_preview_keeps_forms_interactive_without_tokens(): void
    {
        $data = $this->payload();
        unset($data['extra']);
        $data['page_id'] = Page::where('slug', 'contact-us')->value('id');
        $data['message'] = 'A question about brows.';
        $this->postJson('/inquiries/contact', $data)->assertCreated()->assertJsonPath('message', fn ($text) => str_starts_with($text, 'Thank you.'));
        $this->assertDatabaseHas('leads', ['source_page' => '/contact-us', 'message' => 'A question about brows.']);
        config(['starter.preview' => true]);
        $this->postJson('/inquiries/booking', $this->payload())->assertForbidden();
        $this->get('/booking')->assertNotFound();
    }

    public function test_catalog_cache_invalidates_and_seed_preserves_edits(): void
    {
        app(SalonContent::class)->all();
        $service = SalonService::first();
        $service->update(['name' => 'Edited ritual', 'price' => 123]);
        $this->assertNull(Cache::get('salon.catalog'));
        app()->forgetScopedInstances();
        $this->get('/services')->assertSee('Edited ritual')->assertSee('$123');
        $home = Page::where('template', 'salon-home')->firstOrFail();
        $sections = $home->sections;
        foreach ($sections as &$section) {
            if (($section['id'] ?? null) === 'ritual') {
                $section['body'] = 'Edited ritual introduction';
            }
        }
        unset($section);
        $home->update(['sections' => $sections]);
        $image = SalonImage::where('key', 'luxury-ritual-nude')->firstOrFail();
        $image->update(['caption' => 'Edited ritual inspiration']);
        $this->seed(NailSalonSeeder::class);
        $this->assertSame(58, SalonService::count());
        $this->assertSame('Edited ritual', $service->fresh()->name);
        $this->assertCount(2, SalonProfile::find(1)->data['hours']);
        $this->assertSame($sections, $home->fresh()->sections);
        $this->assertSame('Edited ritual inspiration', $image->fresh()->caption);
    }

    public function test_client_export_has_five_routes_no_tokens_or_old_input(): void
    {
        session()->flashInput(['full_name' => 'PRIVATE-GUEST', 'extra' => ['service_id' => 1], 'notes' => 'PRIVATE-NOTE']);
        $name = 'salon-test-'.bin2hex(random_bytes(5));
        $directory = base_path('dist/'.$name);
        try {
            $paths = app(StaticPreviewExporter::class)->export($name);
            $this->assertCount(5, $paths);
            $html = File::get($directory.'/contact-us/index.html');
            $this->assertStringContainsString('noindex, nofollow', $html);
            $this->assertStringContainsString('Send Your Message', $html);
            $this->assertStringContainsString('data-static-preview="true"', $html);
            $this->assertStringNotContainsString('Design preview', $html);
            foreach (['name="_token"', 'name="form_token"', 'PRIVATE-GUEST', 'PRIVATE-NOTE'] as $secret) {
                $this->assertStringNotContainsString($secret, $html);
            }
            $this->assertFileDoesNotExist($directory.'/booking/index.html');
            $logo = Media::findOrFail(app(SiteSettingsService::class)->get('logo_id'));
            foreach ($logo->variants as $variant) {
                $this->assertFileExists($directory.'/storage/'.$variant['path']);
            }
            $this->assertStringContainsString('ks-brand-logo', $html);
            $this->assertDirectoryDoesNotExist($directory.'/originals');
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
