<?php

namespace Tests\Feature;

use App\Filament\Pages\HeaderMenu;
use App\Filament\Pages\WebsiteContent;
use App\Models\Page;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\User;
use App\Services\MediaService;
use App\Services\StaticPreviewExporter;
use Database\Seeders\NailSalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class ClientDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NailSalonSeeder::class);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
    }

    public function test_existing_site_upgrades_without_resetting_owner_content_and_is_repeatable(): void
    {
        $profile = SalonProfile::findOrFail(1);
        $data = $profile->data;
        unset($data['client_design_revision'], $data['contact_image']);
        $data['luxury_revision'] = 'editorial-v5';
        $data['copy']['global']['booking_url'] = '/contact-us';
        $data['copy']['footer']['hours_label'] = 'Salon Hours';
        $data['copy']['footer']['intro'] = 'Owner edited introduction';
        $profile->update(['data' => $data]);
        $kids = SalonCategory::where('slug', 'kids')->firstOrFail();
        $kids->update(['name' => 'Little Luxuries']);
        $service = $kids->services->first();
        $service->update(['description' => 'For children under 12.', 'price' => 42]);
        $home = Page::where('is_home', true)->firstOrFail();
        $blocks = $home->sections;
        $blocks[] = ['id' => 'questions', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'ordering' => 99, 'heading' => 'Legacy FAQ', 'body' => 'Archived questions', 'items' => []];
        $home->update(['sections' => $blocks]);
        $storedSections = $home->fresh()->sections;
        $gallery = SalonImage::where('show_in_gallery', true)->pluck('key')->all();

        $this->seed(NailSalonSeeder::class);
        $upgraded = $profile->fresh()->data;
        $this->assertSame('https://lamournails6484.simplepos.us/new-home', $upgraded['copy']['global']['booking_url']);
        $this->assertSame('Business Hours', $upgraded['copy']['footer']['hours_label']);
        $this->assertSame('Owner edited introduction', $upgraded['copy']['footer']['intro']);
        $this->assertSame($data['hero_slides'], $upgraded['hero_slides']);
        $this->assertSame($storedSections, $home->fresh()->sections);
        $this->assertSame($gallery, SalonImage::where('show_in_gallery', true)->pluck('key')->all());
        $this->assertSame('Kid Services', $kids->fresh()->name);
        $this->assertSame('', $service->fresh()->description);
        $this->assertSame('42.00', $service->fresh()->price);
        $this->get('/')->assertOk()->assertDontSee('Legacy FAQ');
        $this->get('/admin/website-content')->assertOk()->assertDontSee('data-content-section="questions"', false);

        $upgraded['copy']['global']['booking_url'] = 'https://example.com/appointments';
        $upgraded['copy']['footer']['hours_label'] = 'Opening times';
        $profile->update(['data' => $upgraded]);
        $this->seed(NailSalonSeeder::class);
        $this->assertSame($upgraded, $profile->fresh()->data);
    }

    public function test_booking_destination_is_shared_by_every_booking_button(): void
    {
        $destination = 'https://example.com/appointments';
        Livewire::test(HeaderMenu::class)->fillForm(['profile.copy.global.booking_url' => $destination])
            ->call('save')->assertHasNoFormErrors();
        foreach (['/', '/about-us', '/services', '/gallery', '/contact-us'] as $route) {
            $dom = new \DOMDocument;
            @$dom->loadHTML($this->get($route)->assertOk()->getContent());
            $links = (new \DOMXPath($dom))->query('//a[normalize-space(.)="Booking"]');
            $this->assertGreaterThanOrEqual(2, $links->length);
            foreach ($links as $link) {
                $this->assertSame($destination, $link->getAttribute('href'));
            }
        }
        Livewire::test(HeaderMenu::class)->fillForm(['profile.copy.global.booking_url' => '/contact-us'])
            ->call('save')->assertHasFormErrors(['profile.copy.global.booking_url']);
    }

    public function test_contact_photo_can_be_changed_in_admin_without_reintroducing_the_form(): void
    {
        $upload = app(MediaService::class)->upload(UploadedFile::fake()->image('contact-photo.jpg', 180, 240));
        Livewire::withQueryParams(['section' => 'contact'])->test(WebsiteContent::class)
            ->fillForm(['profile.contact_image' => 'uploaded-media-'.$upload->id])
            ->call('save')->assertHasNoFormErrors();
        $this->get('/contact-us')->assertOk()->assertSee($upload->url(), false)->assertDontSee('<form', false);
        $this->get('/admin/website-content?section=contact')->assertOk()->assertSee('Ảnh trang Contact Us')
            ->assertDontSee('Biểu mẫu gửi liên hệ')->assertDontSee('profile.copy.form.');

        $name = 'client-contact-'.bin2hex(random_bytes(4));
        $directory = base_path('dist').DIRECTORY_SEPARATOR.$name;
        $this->assertStringStartsWith(base_path('dist').DIRECTORY_SEPARATOR, $directory);
        try {
            app(StaticPreviewExporter::class)->export($name);
            foreach ($upload->variants as $variant) {
                $this->assertFileExists($directory.'/storage/'.$variant['path']);
            }
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_about_brand_image_can_be_changed_in_admin(): void
    {
        $upload = app(MediaService::class)->upload(UploadedFile::fake()->image('about-brand.jpg', 800, 800));
        Livewire::withQueryParams(['section' => 'about'])->test(WebsiteContent::class)
            ->fillForm(['profile.about_brand_image' => 'uploaded-media-'.$upload->id])
            ->call('save')->assertHasNoFormErrors();

        $this->get('/about-us')->assertOk()->assertSee($upload->url(), false);
        $this->assertSame('uploaded-media-'.$upload->id, SalonProfile::findOrFail(1)->data['about_brand_image']);
        $this->get('/admin/website-content?section=about')->assertOk()->assertSee('Ảnh thương hiệu & lời mời');
    }
}
