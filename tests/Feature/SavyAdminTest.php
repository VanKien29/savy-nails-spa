<?php

namespace Tests\Feature;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\MediaLibrary;
use App\Filament\Pages\SeoSettings;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyNailsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SavyAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SavyNailsSeeder::class);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'cms_role' => 'admin'])->save();
        $this->actingAs($admin);
    }

    public function test_admin_screens_keep_existing_editors_and_savy_branding(): void
    {
        foreach (['/admin', '/admin/website-content', '/admin/header-menu', '/admin/footer-settings', '/admin/seo-settings', '/admin/script-widgets', '/admin/media-library', '/admin/service-menu'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('THE K')->assertDontSee('assets/the-k');
        }
    }

    public function test_shared_contact_hours_and_social_changes_reach_savy_pages(): void
    {
        $socials = [['label' => 'Facebook', 'url' => 'https://facebook.com/example'], ['label' => 'Instagram', 'url' => 'https://instagram.com/example']];
        Livewire::test(FooterSettings::class)->fillForm([
            'settings.hotline' => '509-555-1234', 'settings.address' => 'Updated Savy address',
            'settings.social_links' => $socials,
            'profile.hours' => [['days' => 'Monday', 'time' => '10:00 AM - 06:00 PM']],
        ])->call('save')->assertHasNoFormErrors();
        foreach (['/', '/contact-us'] as $path) {
            $this->get($path)->assertOk()->assertSee('509-555-1234')->assertSee('Updated Savy address')
                ->assertSee('10:00 AM - 06:00 PM')->assertSee('https://instagram.com/example', false);
        }
        Livewire::test(FooterSettings::class)->fillForm(['settings.social_links' => []])->call('save')->assertHasNoFormErrors();
        $this->assertSame([], app(SiteSettingsService::class)->get('social_links'));
        $this->get('/')->assertDontSee('https://instagram.com/example', false);
    }

    public function test_seo_editor_preserves_unrelated_settings_and_media_details_remain_editable(): void
    {
        $settings = app(SiteSettingsService::class);
        $phone = $settings->get('hotline');
        Livewire::test(SeoSettings::class)->fillForm(['default_title' => 'Savy custom default title'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($phone, $settings->get('hotline'));
        $this->assertSame('Savy custom default title', $settings->get('default_title'));
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('savy-test.jpg', 100, 100));
        Livewire::test(MediaLibrary::class)->callAction('details', arguments: ['id' => $media->id], data: ['name' => 'Savy image', 'alt' => 'Savy manicure'])->assertHasNoActionErrors();
        $this->assertSame('Savy manicure', Media::findOrFail($media->id)->alt);
    }
}
