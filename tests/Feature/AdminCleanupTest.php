<?php

namespace Tests\Feature;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\HeaderMenu;
use App\Filament\Pages\SeoSettings;
use App\Filament\Pages\ServiceMenu;
use App\Filament\Pages\SiteSettings;
use App\Models\SalonCategory;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyNailsSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SavyNailsSeeder::class);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
    }

    public function test_theme_form_removes_obsolete_controls_and_preserves_other_settings(): void
    {
        $settings = app(SiteSettingsService::class);
        $before = $settings->all();
        $component = Livewire::test(SiteSettings::class)->assertFormFieldExists('primary_color');
        foreach (['slogan', 'email', 'legal', 'contacts', 'contact_position', 'back_to_top', 'features.contact', 'features.multilingual', 'logo_id', 'default_title', 'hotline'] as $field) {
            $component->assertFormFieldDoesNotExist($field);
        }
        $component->fillForm(['primary_color' => '#123456', 'features.animations' => false])->call('save')->assertHasNoFormErrors();
        $expected = array_replace_recursive($before, ['primary_color' => '#123456', 'features' => ['animations' => false]]);
        $this->assertEquals($expected, $settings->all());
        $this->get('/')->assertOk();
    }

    public function test_moved_seo_and_footer_fields_save_without_changing_booking_or_other_content(): void
    {
        $before = SalonProfile::findOrFail(1)->data;
        Livewire::test(SeoSettings::class)->fillForm([
            'profile.postal_address.postalCode' => '28315',
        ])->call('save')->assertHasNoFormErrors();
        $data = SalonProfile::findOrFail(1)->data;
        $this->assertSame('28315', $data['postal_address']['postalCode']);
        $this->assertSame($before['copy']['global']['booking_url'], $data['copy']['global']['booking_url']);
        $this->assertSame($before['hero_slides'], $data['hero_slides']);
        Livewire::test(FooterSettings::class)->fillForm([
            'profile.copy.global.policy_title' => 'Our privacy policy',
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk();
        $this->assertSame('Our privacy policy', SalonProfile::findOrFail(1)->data['copy']['global']['policy_title']);
        Livewire::test(HeaderMenu::class)->assertFormFieldExists('settings.logo_id');
    }

    public function test_services_can_be_created_without_technical_ids_and_renamed_without_changing_ids(): void
    {
        $category = SalonCategory::firstOrFail();
        $data = ['name' => 'New treatment', 'category_id' => $category->id, 'price' => 29, 'sort_order' => 100, 'active' => true];
        $component = Livewire::test(ServiceMenu::class);
        $component->callAction('createService', data: $data)->assertHasNoActionErrors();
        $component->callAction('createService', data: $data)->assertHasNoActionErrors();
        $services = SalonService::where('name', 'New treatment')->get();
        $this->assertCount(2, $services);
        $this->assertNotSame($services[0]->slug, $services[1]->slug);
        $slug = $services[0]->slug;
        $component->callAction(TestAction::make('editService')->arguments(['id' => $services[0]->id]), data: ['name' => 'Renamed treatment'])->assertHasNoActionErrors();
        $this->assertSame($slug, $services[0]->fresh()->slug);
        $this->get('/services')->assertOk()->assertSee('Renamed treatment');
    }
}
