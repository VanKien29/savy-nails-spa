<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SalonCategory;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyNailsSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SavySiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['starter.preview' => false, 'starter.site_url' => 'https://savy.example']);
        $this->seed(SavyNailsSeeder::class);
    }

    public function test_fresh_install_has_only_savy_public_content_and_contact_booking(): void
    {
        $this->assertSame(31, SalonService::count());
        $this->assertSame(4, SalonCategory::count());
        foreach (['/', '/about-us', '/services', '/gallery', '/contact-us'] as $path) {
            $html = $this->get($path)->assertOk()->assertSee('Savy Nails &amp; Spa', false)
                ->assertSee('savy-reference-', false)->assertDontSee('Fayetteville')->assertDontSee('THE K')->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML($html);
            $this->assertCount(1, $dom->getElementsByTagName('h1'));
            $this->assertStringNotContainsString('the-k/', $html);
        }
        $this->get('/services')->assertSee('Volcano Spa Pedicure')->assertSee('$75')->assertSee('$....');
        $this->get('/contact-us')->assertDontSee('<form', false)->assertSee('tel:5095297289', false);
        $this->get('/booking')->assertNotFound();
    }

    public function test_repeated_seed_preserves_edited_savy_menu_ids_prices_and_admin_credentials(): void
    {
        $service = SalonService::firstOrFail();
        $service->update(['price' => 123.45, 'description' => 'Custom Savy treatment']);
        $ids = SalonService::orderBy('id')->pluck('id')->all();
        $admin = User::factory()->create(['email' => 'owner@savy.example']);
        $admin->forceFill(['is_admin' => true])->save();
        $hash = $admin->password;
        $settings = app(SiteSettingsService::class);
        $customImage = app(MediaService::class)->upload(UploadedFile::fake()->image('savy-custom-share.png', 100, 100), 'Custom Savy share image');
        $settings->update(array_replace($settings->all(), ['social_links' => [], 'og_media_id' => $customImage->id]));
        $home = Page::where('is_home', true)->firstOrFail();
        $home->update(['og_media_id' => $customImage->id]);
        $this->seed(SavyNailsSeeder::class);
        $this->assertSame($ids, SalonService::orderBy('id')->pluck('id')->all());
        $this->assertSame('123.45', $service->fresh()->price);
        $this->assertSame('Custom Savy treatment', $service->fresh()->description);
        $this->assertSame($hash, $admin->fresh()->password);
        $this->assertSame('owner@savy.example', $admin->fresh()->email);
        $this->assertSame([], $settings->get('social_links'));
        $this->assertSame($customImage->id, $settings->get('og_media_id'));
        $this->assertSame($customImage->id, $home->fresh()->og_media_id);
    }

    public function test_cleanup_backs_up_and_repairs_old_home_and_page_metadata_without_resetting_menu(): void
    {
        $page = Page::where('slug', 'home')->firstOrFail();
        $page->update(['template' => 'salon-home', 'seo_title' => 'THE K Luxury Nail Bar', 'seo_description' => 'Visit us in Fayetteville, NC.']);
        Page::where('slug', 'about-us')->firstOrFail()->update(['seo_title' => 'About THE K']);
        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace($settings->all(), ['company_name' => 'THE K LUXURY NAIL BAR']));
        $profile = SalonProfile::findOrFail(1);
        $data = $profile->data;
        unset($data['savy_revision']);
        $data['postal_address'] = ['streetAddress' => '2804 Freedom Pkwy Dr', 'addressLocality' => 'Fayetteville', 'addressRegion' => 'NC', 'postalCode' => '28314', 'addressCountry' => 'US'];
        $data['timezone'] = 'America/New_York';
        $data['hours'] = [['days' => 'Monday - Saturday', 'time' => '09:30 AM - 07:30 PM']];
        $profile->update(['data' => $data]);
        $service = SalonService::firstOrFail();
        $service->update(['price' => 99]);
        $ids = SalonService::orderBy('id')->pluck('id')->all();
        $this->artisan('savy:cleanup')->assertSuccessful();
        $this->assertSame('salon-home', $page->fresh()->template);
        $this->artisan('savy:cleanup', ['--apply' => true])->assertSuccessful();
        $this->assertSame('salon-savy-home', $page->fresh()->template);
        $this->assertStringContainsString('Savy', $page->fresh()->seo_title);
        $this->assertStringContainsString('Savy', Page::where('slug', 'about-us')->firstOrFail()->seo_title);
        $this->assertSame($ids, SalonService::orderBy('id')->pluck('id')->all());
        $this->assertSame('99.00', $service->fresh()->price);
        $this->assertSame('WA', $profile->fresh()->data['postal_address']['addressRegion']);
        $this->assertSame('99362', $profile->fresh()->data['postal_address']['postalCode']);
        $this->assertSame('America/Los_Angeles', $profile->fresh()->data['timezone']);
        $this->assertSame('09:00 AM - 07:00 PM', $profile->fresh()->data['hours'][0]['time']);
        $this->assertNotEmpty(Storage::disk('local')->files('backups'));
        $this->assertFalse(DB::table('page_revisions')->where('snapshot', 'like', '%THE K%')->exists());
        $this->artisan('savy:cleanup', ['--apply' => true])->assertSuccessful();
        $this->assertSame(31, SalonService::count());
    }

    public function test_legacy_catalog_requires_explicit_replacement_and_becomes_the_approved_savy_menu(): void
    {
        SalonService::query()->delete();
        SalonCategory::query()->delete();
        $category = SalonCategory::create(['name' => 'Old menu', 'slug' => 'pedicures', 'description' => '', 'sort_order' => 0]);
        SalonService::create(['name' => 'Golden 99K Fairy Queen', 'slug' => 'old-royal-pedicure', 'category_id' => $category->id, 'price' => 99, 'active' => true]);
        $this->artisan('savy:cleanup', ['--apply' => true])->assertFailed();
        $this->assertSame(1, SalonService::count());
        $this->artisan('savy:cleanup', ['--apply' => true, '--replace-legacy-menu' => true])->assertSuccessful();
        $this->assertSame(31, SalonService::count());
        $this->assertSame(4, SalonCategory::count());
        $this->assertDatabaseHas('salon_services', ['name' => 'Volcano Spa Pedicure', 'price' => 75]);
        $this->assertDatabaseMissing('salon_services', ['name' => 'Golden 99K Fairy Queen']);
    }

    public function test_adding_a_category_named_nails_does_not_make_an_existing_savy_menu_legacy(): void
    {
        SalonCategory::create(['name' => 'Savy custom nails', 'slug' => 'nails', 'description' => '', 'sort_order' => 99]);
        $ids = SalonService::orderBy('id')->pluck('id')->all();
        $this->artisan('savy:cleanup', ['--apply' => true, '--replace-legacy-menu' => true])->assertSuccessful();
        $this->assertSame($ids, SalonService::orderBy('id')->pluck('id')->all());
        $this->assertDatabaseHas('salon_categories', ['slug' => 'nails']);
    }

    public function test_cleanup_refuses_to_change_content_if_the_private_backup_cannot_be_written(): void
    {
        $page = Page::where('is_home', true)->firstOrFail();
        $page->update(['template' => 'salon-home', 'seo_title' => 'Owner title before migration']);
        $ids = SalonService::orderBy('id')->pluck('id')->all();
        $failedDisk = \Mockery::mock(FilesystemAdapter::class);
        $failedDisk->shouldReceive('put')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('local')->andReturn($failedDisk);
        $this->artisan('savy:cleanup', ['--apply' => true])->assertFailed();
        $this->assertSame('salon-home', $page->fresh()->template);
        $this->assertSame('Owner title before migration', $page->fresh()->seo_title);
        $this->assertSame($ids, SalonService::orderBy('id')->pluck('id')->all());
    }
}
