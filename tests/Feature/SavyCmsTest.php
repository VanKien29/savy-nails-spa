<?php

namespace Tests\Feature;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\HeaderMenu;
use App\Filament\Pages\ServiceMenu;
use App\Filament\Pages\WebsiteContent;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\MediaService;
use Database\Seeders\SavyCmsSeeder;
use Database\Seeders\SavyNailsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SavyCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SavyNailsSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true, 'is_active' => true])->save();
        $this->actingAs($user);
    }

    public function test_forms_match_the_approved_client_and_locate_the_savy_home(): void
    {
        $component = Livewire::test(WebsiteContent::class)->assertOk()
            ->assertSet('data.blocks.savy-hero.heading', 'Nail Salon in Walla Walla, WA 99362')
            ->assertSee('04. Your Savy Moment')->assertSee('06. FAQ');
        $this->assertSame('/', $component->instance()->clientUrl());
        foreach (['home' => '/', 'about' => '/about-us', 'services' => '/services', 'gallery' => '/gallery', 'contact' => '/contact-us'] as $area => $path) {
            $form = Livewire::withQueryParams(['section' => $area])->test(WebsiteContent::class)->assertOk();
            $this->assertSame($path, $form->instance()->clientUrl());
            $this->get($path)->assertOk();
        }
        Livewire::test(HeaderMenu::class)->assertOk();
        Livewire::test(FooterSettings::class)->assertOk()->assertDontSee('Chính sách chân trang');
    }

    public function test_home_saves_text_images_buttons_and_visibility_without_affecting_other_pages(): void
    {
        $upload = app(MediaService::class)->upload(UploadedFile::fake()->image('cms-hero.jpg', 100, 80), 'New hero from CMS');
        $about = Page::where('slug', 'about-us')->first()->sections;
        Livewire::test(WebsiteContent::class)->fillForm([
            'blocks.savy-hero.heading' => 'A new Savy welcome',
            'blocks.savy-intro.body' => 'Introduction written in Admin',
            'blocks.savy-feature.button_label' => 'Visit our salon',
            'blocks.savy-feature.button_url' => '/about-us',
            'profile.savy.images.hero' => 'uploaded-media-'.$upload->id,
            'blocks.savy-faq.items' => [['title' => 'A custom question', 'value' => 'custom', 'text' => 'A custom answer']],
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk()->assertSee('A new Savy welcome')->assertSee('Introduction written in Admin')
            ->assertSee('Visit our salon')->assertSee('New hero from CMS')->assertSee('A custom answer');
        $this->assertSame($about, Page::where('slug', 'about-us')->first()->sections);
        $this->assertDatabaseHas('salon_images', ['key' => 'uploaded-media-'.$upload->id, 'media_id' => $upload->id]);
        $upload->update(['alt' => 'Alt edited after selecting the image']);
        $this->get('/')->assertOk()->assertSee('Alt edited after selecting the image');
        Livewire::test(WebsiteContent::class)->fillForm(['blocks.savy-feature.enabled' => false, 'blocks.savy-faq.items' => []])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk()->assertDontSee('class="av-feature av-container"', false)->assertDontSee('A custom answer');
        Livewire::test(WebsiteContent::class)->assertSet('data.blocks.savy-faq.items', []);
    }

    public function test_about_banner_cards_and_images_are_editable_and_card_deletions_stay_deleted(): void
    {
        Livewire::withQueryParams(['section' => 'about'])->test(WebsiteContent::class)->fillForm([
            'blocks.savy-banner.heading' => 'Meet our salon',
            'profile.savy.images.about_banner' => 'savy-gallery-3',
            'blocks.savy-intro.body' => 'A new about introduction',
            'blocks.savy-strengths.items' => [['title' => 'Care from Admin', 'text' => 'An updated card', 'label' => 'savy-gallery-7']],
            'blocks.savy-strengths.button_label' => 'Explore treatments', 'blocks.savy-strengths.button_url' => '/services',
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/about-us')->assertOk()->assertSee('Meet our salon')->assertSee('A new about introduction')
            ->assertSee('An updated card')->assertSee('data-salon-image="savy-gallery-7"', false)->assertDontSee('A Moment for You');
        Livewire::withQueryParams(['section' => 'about'])->test(WebsiteContent::class)->fillForm(['blocks.savy-strengths.items' => []])->call('save')->assertHasNoFormErrors();
        $this->get('/about-us')->assertOk()->assertDontSee('An updated card');
    }

    public function test_gallery_reorders_uploaded_images_and_can_be_cleared_independently_of_home(): void
    {
        $home = SalonProfile::find(1)->data['savy']['home_gallery'];
        $upload = app(MediaService::class)->upload(UploadedFile::fake()->image('cms-gallery.jpg', 90, 70), 'A gallery upload');
        $keys = ['savy-gallery-10', 'uploaded-media-'.$upload->id, 'savy-gallery-2'];
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)->fillForm(['gallery_images' => $keys])->call('save')->assertHasNoFormErrors();
        $response = $this->get('/gallery')->assertOk()->assertSee('A gallery upload');
        $this->assertSame($keys, SalonProfile::find(1)->data['savy']['gallery']);
        $this->assertSame($home, SalonProfile::find(1)->data['savy']['home_gallery']);
        $this->assertLessThan(strpos($response->getContent(), 'data-lightbox-file="savy-gallery-2"'), strpos($response->getContent(), 'data-lightbox-file="savy-gallery-10"'));
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)->fillForm(['gallery_images' => []])->call('save')->assertHasNoFormErrors();
        $this->get('/gallery')->assertOk()->assertDontSee('data-lightbox-file=');
        $this->assertSame([], SalonProfile::find(1)->data['savy']['gallery']);
        $this->assertDatabaseHas('salon_images', ['key' => 'savy-gallery-10']);
        $this->seed(SavyCmsSeeder::class);
        $this->assertSame([], SalonProfile::find(1)->data['savy']['gallery']);
    }

    public function test_header_saves_internal_contact_link_menu_and_responsive_logos(): void
    {
        $desktop = app(MediaService::class)->upload(UploadedFile::fake()->image('desktop.jpg', 120, 80));
        $mobile = app(MediaService::class)->upload(UploadedFile::fake()->image('mobile.jpg', 90, 90));
        Livewire::test(HeaderMenu::class)->fillForm([
            'settings.logo_id' => $desktop->id, 'settings.mobile_logo_id' => $mobile->id, 'settings.favicon_id' => $mobile->id,
            'links' => [['label' => 'Our Treatments', 'url' => '/services', 'enabled' => true], ['label' => 'Hidden Link', 'url' => '/gallery', 'enabled' => false]],
            'profile.copy.global.booking_label' => 'Contact Savy', 'profile.copy.global.booking_url' => '/contact-us',
        ])->call('save')->assertHasNoFormErrors();
        foreach (['/', '/about-us', '/contact-us'] as $path) {
            $this->get($path)->assertOk()->assertSee('Our Treatments')->assertSee('Contact Savy')->assertDontSee('Hidden Link')
                ->assertSee($desktop->url(), false)->assertSee($mobile->url(), false);
        }
        $this->assertSame(5, NavigationItem::where('location', 'footer')->count());
    }

    public function test_footer_contact_and_faq_share_live_contact_details_and_preserve_unrelated_images(): void
    {
        $hero = SalonProfile::find(1)->data['savy']['images']['hero'];
        $upload = app(MediaService::class)->upload(UploadedFile::fake()->image('footer.jpg', 90, 70));
        Livewire::test(FooterSettings::class)->fillForm([
            'settings.company_name' => 'Updated Savy Company', 'settings.copyright' => 'A custom copyright',
            'settings.hotline' => '509-555-1234', 'settings.address' => 'A new salon address',
            'profile.savy.footer.contact_label' => 'Visit Savy Today',
            'profile.savy.images.footer_background' => 'uploaded-media-'.$upload->id,
            'profile.hours' => [['days' => 'Tuesday', 'time' => '10:00 AM - 06:00 PM']],
            'settings.social_links' => [['label' => 'Instagram', 'url' => 'https://instagram.com/new-savy']],
        ])->call('save')->assertHasNoFormErrors();
        $this->assertSame($hero, SalonProfile::find(1)->data['savy']['images']['hero']);
        $this->get('/')->assertOk()->assertSee('Visit Savy Today')->assertSee('Updated Savy Company')->assertSee('A custom copyright')
            ->assertSee('Tuesday: 10:00 AM - 06:00 PM.')->assertSee($upload->url(), false);
        $this->get('/contact-us')->assertOk()->assertSee('A new salon address')->assertSee('509-555-1234')->assertSee('https://instagram.com/new-savy', false);
        Livewire::withQueryParams(['section' => 'contact'])->test(WebsiteContent::class)->fillForm([
            'blocks.savy-card.heading' => 'Book directly with us', 'blocks.savy-card.body' => 'Updated appointment message',
            'settings.hotline' => '509-555-4567',
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/contact-us')->assertOk()->assertSee('Book directly with us')->assertSee('Updated appointment message');
        $this->get('/')->assertOk()->assertSee('509-555-4567')->assertSee('Visit Savy Today')->assertSee($upload->url(), false);
    }

    public function test_service_editor_updates_prices_details_order_and_visibility_on_client(): void
    {
        $service = SalonService::where('name', 'Regular Manicure')->firstOrFail();
        Livewire::test(ServiceMenu::class)->callAction('editService', arguments: ['id' => $service->id], data: [
            'name' => 'CMS Manicure', 'category_id' => $service->category_id, 'price' => 49, 'fill_price' => 35, 'gel_price' => 60,
            'price_from' => true, 'description' => 'Service updated in Admin', 'price_note' => 'Custom price note',
            'includes_label' => 'Care steps', 'included_steps' => "First care step\nSecond care step", 'duration_label' => '45 minutes', 'active' => true,
        ])->assertHasNoActionErrors();
        foreach (['/', '/services'] as $path) {
            $this->get($path)->assertOk()->assertSee('CMS Manicure')->assertSee('$49+')->assertSee('$35')->assertSee('$60')
                ->assertSee('Custom price note')->assertSee('First care step')->assertSee('45 minutes');
        }
        Livewire::test(ServiceMenu::class)->call('toggleService', $service->id);
        $this->get('/services')->assertOk()->assertDontSee('CMS Manicure');
    }

    public function test_page_seo_and_invalid_image_validation_do_not_change_other_page_content(): void
    {
        $original = Page::where('is_home', true)->first()->sections;
        Livewire::withQueryParams(['section' => 'services'])->test(WebsiteContent::class)->fillForm([
            'page.seo_title' => 'A new Services SEO title', 'page.seo_description' => 'A Services description from Admin',
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/services')->assertOk()->assertSee('<title>A new Services SEO title</title>', false)->assertSee('A Services description from Admin');
        $this->assertSame($original, Page::where('is_home', true)->first()->sections);
        Livewire::withQueryParams(['section' => 'home'])->test(WebsiteContent::class)->fillForm(['profile.savy.home_gallery' => ['unknown-image']])->call('save')->assertHasFormErrors(['profile.savy.home_gallery']);
        Livewire::withQueryParams(['section' => 'home'])->test(WebsiteContent::class)->fillForm(['profile.savy.images.hero' => 'unknown-image'])->call('save')->assertHasFormErrors(['profile.savy.images.hero']);
        Livewire::withQueryParams(['section' => 'home'])->test(WebsiteContent::class)->fillForm(['blocks.savy-feature.button_url' => 'javascript:alert(1)'])->call('save')->assertHasFormErrors(['blocks.savy-feature.button_url']);
        Livewire::withQueryParams(['section' => 'home'])->test(WebsiteContent::class)->fillForm(['blocks.savy-intro.body' => '<script>alert("unsafe")</script>'])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert("unsafe")</script>', false);
    }
}
