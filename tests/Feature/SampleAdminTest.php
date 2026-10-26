<?php

namespace Tests\Feature;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\HeaderMenu;
use App\Filament\Pages\MediaLibrary;
use App\Filament\Pages\SeoSettings;
use App\Filament\Pages\WebsiteContent;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Database\Seeders\NailSalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SampleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NailSalonSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true, 'cms_role' => 'admin'])->save();
        $this->actingAs($user);
    }

    public function test_sample_admin_screens_render_and_sections_are_named_for_users(): void
    {
        foreach (['/admin', '/admin/website-content', '/admin/header-menu', '/admin/footer-settings', '/admin/seo-settings', '/admin/script-widgets', '/admin/media-library'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/website-content')->assertSee('Chỉnh mọi chữ và ảnh trên website')->assertSee('Chọn ảnh từ Thư viện Media');
    }

    public function test_website_editor_updates_home_without_replacing_other_blocks(): void
    {
        $page = Page::where('is_home', true)->firstOrFail();
        $original = collect($page->sections)->firstWhere('id', 'journey');
        Livewire::test(WebsiteContent::class)->fillForm(['blocks.hero.heading' => 'Updated from sample editor'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($original, collect($page->fresh()->sections)->firstWhere('id', 'journey'));
        $this->get('/')->assertOk()->assertSee('Updated from sample editor');
    }

    public function test_removing_repeater_items_does_not_restore_deleted_items(): void
    {
        $page = Page::where('is_home', true)->firstOrFail();
        $items = collect($page->sections)->firstWhere('id', 'journey')['items'];
        Livewire::test(WebsiteContent::class)->fillForm(['blocks.journey.items' => array_slice($items, 0, 1)])->call('save')->assertHasNoFormErrors();
        $this->assertCount(1, collect($page->fresh()->sections)->firstWhere('id', 'journey')['items']);
    }

    public function test_gallery_configuration_and_copy_save_from_the_selected_section(): void
    {
        $lookbook = SalonProfile::find(1)->data['home_lookbook'];
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)->fillForm(['profile.copy.gallery.social_heading' => 'Our new inspiration'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($lookbook, SalonProfile::find(1)->data['home_lookbook']);
        $this->get('/gallery')->assertOk()->assertSee('Our new inspiration');
    }

    public function test_content_editor_sections_follow_the_actual_home_order_and_keep_related_fields_together(): void
    {
        $page = Page::where('is_home', true)->firstOrFail();
        $page->update(['sections' => collect($page->sections)->map(function (array $block) {
            $block['ordering'] = $block['id'] === 'journey' ? 0 : 10;

            return $block;
        })->all()]);
        $response = $this->get('/admin/website-content')->assertOk()->assertDontSee('Trang chủ — nút & ghi chú');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $tabs = $xpath->query('//button[@role="tab" and @data-content-section]');
        $actual = [];
        foreach ($tabs as $tab) {
            $actual[] = $tab->getAttribute('data-content-section');
        }
        $this->assertSame(collect($page->sections)->sortBy('ordering')->pluck('id')->values()->all(), $actual);
        $hero = $dom->saveHTML($xpath->query('//div[@role="tabpanel" and @data-content-section="hero"]')->item(0));
        $intro = $dom->saveHTML($xpath->query('//div[@role="tabpanel" and @data-content-section="intro"]')->item(0));
        $this->assertStringContainsString('profile.copy.home.menu_label', $hero);
        $this->assertStringNotContainsString('profile.copy.home.story_label', $hero);
        $this->assertStringContainsString('profile.copy.home.story_label', $intro);
        Livewire::test(WebsiteContent::class)->fillForm(['blocks.collection.enabled' => false])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk()->assertDontSee('kl-collection-grid', false);
        $this->get('/admin/website-content')->assertOk()->assertSee('Ẩn');
    }

    public function test_contact_sections_save_shared_details_and_page_copy_without_changing_other_settings(): void
    {
        $settings = app(SiteSettingsService::class);
        $logo = $settings->get('logo_id');
        Livewire::withQueryParams(['section' => 'contact'])->test(WebsiteContent::class)->fillForm([
            'settings.hotline' => '910-555-4567', 'settings.address' => 'New salon address',
            'profile.copy.contact.heading' => 'Contact our team',
            'profile.copy.contact.map_heading' => 'Visit our salon',
            'profile.hours' => [['days' => 'Monday', 'time' => '10:00 AM – 06:00 PM']],
        ])->call('save')->assertHasNoFormErrors();
        $this->assertSame($logo, $settings->get('logo_id'));
        $this->get('/contact-us')->assertOk()->assertSee('Contact our team')->assertSee('Visit our salon')->assertSee('910-555-4567')->assertSee('New salon address');
        $this->get('/')->assertOk()->assertSee('910-555-4567')->assertSee('10:00 AM – 06:00 PM');
    }

    public function test_about_sections_save_story_values_and_brand_content_in_their_visible_locations(): void
    {
        $component = Livewire::withQueryParams(['section' => 'about'])->test(WebsiteContent::class);
        $this->assertSame('/about-us', $component->instance()->clientUrl());
        $component->fillForm([
            'blocks.story.heading' => 'Our new story', 'blocks.promise.body' => 'A second story paragraph',
            'blocks.promise.heading' => 'Our values', 'profile.copy.about.brand_heading' => 'Our brand',
        ])->call('save')->assertHasNoFormErrors();
        $response = $this->get('/about-us')->assertOk()->assertSee('A second story paragraph');
        $this->assertLessThan(strpos($response->getContent(), 'Our values'), strpos($response->getContent(), 'Our new story'));
        $this->assertLessThan(strpos($response->getContent(), 'Our brand'), strpos($response->getContent(), 'Our values'));
    }

    public function test_footer_changes_contact_information_and_navigation_atomically(): void
    {
        Livewire::test(FooterSettings::class)->fillForm(['settings.hotline' => '910-555-1234', 'profile.copy.footer.intro' => 'Footer from sample CMS', 'links' => [['label' => 'Our Treatments', 'url' => '/services', 'enabled' => true]]])->call('save')->assertHasNoFormErrors();
        $this->assertSame('910-555-1234', app(SiteSettingsService::class)->get('hotline'));
        $this->assertSame(1, NavigationItem::where('location', 'footer')->count());
        $this->get('/')->assertOk()->assertSee('Footer from sample CMS')->assertSee('Our Treatments');
    }

    public function test_social_channel_selection_previews_and_saves_the_matching_footer_icons(): void
    {
        $links = array_map(fn (string $channel) => [
            'label' => $channel, 'url' => 'https://example.com/social/'.strtolower($channel),
        ], array_reverse(array_keys(SiteSettingsService::SOCIAL_CHANNELS)));
        $settings = app(SiteSettingsService::class);
        $hotline = $settings->get('hotline');
        foreach ([[FooterSettings::class, 'settings.social_links']] as [$page, $field]) {
            Livewire::test($page)->assertSee('Kênh / icon')->assertSee('data-icon="facebook"', false)
                ->fillForm([$field => $links])->call('save')->assertHasNoFormErrors();
            $this->assertEquals($links, $settings->get('social_links'));
            $dom = new \DOMDocument;
            @$dom->loadHTML($this->get('/')->assertOk()->getContent());
            $icons = (new \DOMXPath($dom))->query('//footer//div[@class="ks-socials"]/a');
            $this->assertCount(count($links), $icons);
            foreach ($links as $index => $link) {
                $this->assertSame($link['url'], $icons[$index]->getAttribute('href'));
                $svg = $icons[$index]->getElementsByTagName('svg')->item(0);
                $this->assertSame(strtolower($link['label']), $svg->getAttribute('data-icon'));
                $this->assertGreaterThan(0, $svg->getElementsByTagName('path')->length + $svg->getElementsByTagName('rect')->length);
            }
        }
        Livewire::test(FooterSettings::class)->fillForm(['settings.social_links' => []])->call('save')->assertHasNoFormErrors();
        $this->assertSame([], $settings->get('social_links'));
        $this->assertSame($hotline, $settings->get('hotline'));
    }

    public function test_seo_form_preserves_settings_not_edited_on_that_screen(): void
    {
        $hotline = app(SiteSettingsService::class)->get('hotline');
        Livewire::test(SeoSettings::class)->fillForm(['default_title' => 'Updated SEO default'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated SEO default', app(SiteSettingsService::class)->get('default_title'));
        $this->assertSame($hotline, app(SiteSettingsService::class)->get('hotline'));
    }

    public function test_media_grid_details_can_update_alt_text(): void
    {
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('grid-test.jpg', 100, 100));
        Livewire::test(MediaLibrary::class)->callAction('details', arguments: ['id' => $media->id], data: ['name' => 'Updated image title', 'alt' => 'New alt text'])->assertHasNoActionErrors();
        $this->assertSame('New alt text', $media->fresh()->alt);
    }

    public function test_existing_admin_accounts_can_use_all_settings_forms(): void
    {
        $editor = User::factory()->create();
        $editor->forceFill(['is_admin' => true, 'cms_role' => 'editor'])->save();
        $this->actingAs($editor);
        $this->get('/admin/website-content')->assertOk();
        $this->get('/admin/media-library')->assertOk();
        $this->get('/admin/footer-settings')->assertOk();
        $this->get('/admin/seo-settings')->assertOk();
        Livewire::test(FooterSettings::class)->fillForm(['social_links' => []])->call('save')->assertHasNoFormErrors();
        $editor->forceFill(['cms_role' => 'support'])->save();
        $this->get('/admin/website-content')->assertOk();
        Livewire::test(MediaLibrary::class)->assertOk();
    }

    public function test_header_editor_updates_links_and_booking_button_without_changing_footer(): void
    {
        $footer = NavigationItem::where('location', 'footer')->orderBy('sort_order')->pluck('url')->all();
        Livewire::test(HeaderMenu::class)->fillForm(['links' => [['label' => 'Our Menu', 'url' => '/services', 'enabled' => true]], 'profile.copy.global.booking_label' => 'Visit Us'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(1, NavigationItem::where('location', 'header')->count());
        $this->assertSame($footer, NavigationItem::where('location', 'footer')->orderBy('sort_order')->pluck('url')->all());
        $this->get('/')->assertOk()->assertSee('Our Menu')->assertSee('Visit Us');
    }

    public function test_content_area_labels_match_the_client_and_old_gallery_page_redirects(): void
    {
        $this->assertSame(['Home', 'About Us', 'Services', 'Gallery', 'Contact Us'], array_values((new WebsiteContent)->groups()));
        $this->get('/admin/website-content')->assertOk()->assertDontSee('href="http://localhost/admin/salon-images"', false)->assertDontSee('section=header', false)->assertDontSee('section=policy', false);
        foreach (['header', 'policy'] as $removedSection) {
            Livewire::withQueryParams(['section' => $removedSection])->test(WebsiteContent::class)->assertSet('section', 'home');
        }
        $this->get('/admin/salon-images')->assertRedirect('/admin/website-content?section=gallery');
        $this->get('/admin/pages')->assertRedirect('/admin/website-content');
        $this->get('/admin/pages/create')->assertRedirect('/admin/website-content');
        $this->get('/admin/pages/1/edit')->assertRedirect('/admin/website-content');
    }

    public function test_gallery_images_are_managed_inside_website_content(): void
    {
        $gallery = SalonImage::where('show_in_gallery', true)->orderBy('sort_order')->firstOrFail();
        $metadata = $gallery->only(['caption', 'alt', 'category', 'source_url', 'source_credit']);
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)
            ->fillForm(['gallery_images' => [$gallery->key]])->call('save')->assertHasNoFormErrors();
        $this->assertSame(1, SalonImage::where('show_in_gallery', true)->count());
        $this->get('/gallery')->assertOk()->assertSee($gallery->alt);
        $this->assertSame($metadata, $gallery->fresh()->only(['caption', 'alt', 'category', 'source_url', 'source_credit']));
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)
            ->fillForm(['gallery_images' => []])->call('save')->assertHasNoFormErrors();
        $this->assertSame(0, SalonImage::where('show_in_gallery', true)->count());
        $this->assertDatabaseHas('salon_images', ['id' => $gallery->id]);
    }

    public function test_gallery_editor_matches_main_without_filters_or_source_fields(): void
    {
        foreach (['/admin/website-content?section=gallery'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Bộ lọc Gallery')
                ->assertDontSee('Nhóm ảnh')->assertDontSee('Đường dẫn nguồn')->assertDontSee('Tác giả / nguồn ảnh')
                ->assertDontSee('Chú thích khi mở ảnh lớn')->assertDontSee('Nút phóng to')->assertDontSee('Ghi chú dưới thư viện ảnh');
        }
        $response = $this->get('/gallery')->assertOk()->assertDontSee('ks-gallery-filters')
            ->assertDontSee('data-gallery-filter')->assertDontSee('ks-image-source')
            ->assertDontSee('Style inspiration')->assertDontSee('Treatment collection');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(SalonImage::where('show_in_gallery', true)->count(), $xpath->query('//figure/picture'));
        $this->assertCount(0, $xpath->query('//figure//a|//figure//button|//dialog[@id="image-lightbox"]'));
    }

    public function test_gallery_accepts_uploaded_images_without_category_or_source_and_saves_order(): void
    {
        $existing = SalonImage::where('show_in_gallery', true)->firstOrFail();
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('new-gallery.jpg', 120, 80));
        $key = 'uploaded-media-'.$media->id;
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)->fillForm(['gallery_images' => [
            $key, $existing->key,
        ]])->call('save')->assertHasNoFormErrors();
        $this->assertSame([$key, $existing->key], SalonImage::where('show_in_gallery', true)->orderBy('sort_order')->pluck('key')->all());
        $this->assertSame($media->name, SalonImage::where('key', $key)->firstOrFail()->caption);
        $this->get('/gallery')->assertOk()->assertSee($media->url(), false);
    }

    public function test_invalid_gallery_selections_do_not_replace_the_live_images(): void
    {
        $before = SalonImage::where('show_in_gallery', true)->orderBy('sort_order')->pluck('key')->all();
        Livewire::withQueryParams(['section' => 'gallery'])->test(WebsiteContent::class)
            ->fillForm(['gallery_images' => ['unknown-image']])->call('save')->assertHasFormErrors(['gallery_images.0']);
        $this->assertSame($before, SalonImage::where('show_in_gallery', true)->orderBy('sort_order')->pluck('key')->all());
    }

    public function test_uploaded_media_can_be_selected_directly_in_website_content(): void
    {
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('direct-content.jpg', 120, 80), 'Uploaded image');
        Livewire::test(WebsiteContent::class)->fillForm(['profile.story_image' => 'uploaded-media-'.$media->id])->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseHas('salon_images', ['key' => 'uploaded-media-'.$media->id, 'media_id' => $media->id]);
        $this->get('/')->assertOk()->assertSee($media->url(), false);
    }
}
