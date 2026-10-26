<?php

namespace Tests\Feature;

use App\Filament\Pages\WebsiteContent;
use App\Filament\Pages\ServiceMenu;
use App\Models\SalonCategory;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use Database\Seeders\NailSalonSeeder;
use Database\Seeders\SalonClientMenuSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NailSalonSeeder::class);
    }

    public function test_customer_menu_prices_order_and_treatments_render(): void
    {
        $this->assertSame(55, SalonService::where('active', true)->count());
        $this->assertSame(['brows-lashes', 'nails', 'pedicures', 'kids', 'waxing'], SalonCategory::orderBy('sort_order')->pluck('slug')->all());
        $this->assertSame('350.00', SalonService::where('name', 'Ombre Brows')->firstOrFail()->price);
        $this->assertSame('600.00', SalonService::where('name', 'VIP Hair Stroke Brows')->firstOrFail()->price);
        $this->assertSame('35.00', SalonCategory::where('slug', 'kids')->firstOrFail()->services()->where('name', 'Basic Pedicure')->firstOrFail()->price);
        $this->assertSame('70.00', SalonService::where('name', 'Espresso Spa Pedicure')->firstOrFail()->gel_price);
        $this->assertSame('45.00', SalonService::where('slug', 'nails-full-set-acrylic-with-gel-color-powder')->firstOrFail()->fill_price);
        $this->assertNull(SalonService::where('name', 'Ombre')->firstOrFail()->fill_price);
        $response = $this->get('/services')->assertOk()
            ->assertSeeInOrder(['Brow Services', 'Permanent Makeup', 'Lash Services', 'Nail Enhancements', 'Dip Powder', 'Manicures', 'The K Signature Royal Pedicure', 'Rosé Champagne Bliss Pedicure', 'Golden 99K Fairy Queen Pedicure', 'Mint Refresh Pedicure', 'Kid’s Services', 'Waxing'])
            ->assertSee('Full Set')->assertSee('Fill-in')->assertSee('50% of Full Set Price')
            ->assertSee('25-Minute Massage')->assertSee('Complimentary Warm Paraffin Hand Treatment (Optional)')
            ->assertDontSee('Fresh Orange Pedicure')->assertDontSee('Ask about gel polish');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(55, $xpath->query('//*[@data-service-row]'));
        $this->assertCount(14, $xpath->query('//*[@data-service-row][.//*[contains(text(),"The K Signature Royal Pedicure")]]//li'));
        $this->assertCount(3, $xpath->query('//div[@class="ks-menu-brow-photos"]//img'));
    }

    public function test_existing_menu_upgrades_in_place_and_does_not_reset_later_edits(): void
    {
        $profile = SalonProfile::findOrFail(1);
        $data = $profile->data;
        unset($data['menu_revision']);
        $profile->update(['data' => $data]);
        $service = SalonService::where('slug', 'nails-full-set-acrylic-with-gel-color-powder')->firstOrFail();
        $service->update(['price' => 99, 'fill_price' => null]);
        $retired = SalonService::create(['category_id' => $service->category_id, 'slug' => 'nails-fill-in-acrylic-with-gel', 'name' => 'Acrylic with Gel', 'price' => 45, 'sort_order' => 100, 'active' => true]);
        $this->seed(SalonClientMenuSeeder::class);
        $this->assertSame($service->id, $service->fresh()->id);
        $this->assertSame('55.00', $service->fresh()->price);
        $this->assertFalse($retired->fresh()->active);
        $this->assertSame(55, SalonService::where('active', true)->count());
        $service->update(['price' => 58, 'fill_price' => 48]);
        $data = $profile->fresh()->data;
        $data['menu_brow_images'] = [];
        $profile->update(['data' => $data]);
        $this->seed(NailSalonSeeder::class);
        $this->assertSame('58.00', $service->fresh()->price);
        $this->assertSame('48.00', $service->fresh()->fill_price);
        $this->assertFalse($retired->fresh()->active);
        $this->assertSame([], $profile->fresh()->data['menu_brow_images']);
    }

    public function test_admin_can_edit_paired_prices_and_menu_photos(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
        $service = SalonService::where('slug', 'nails-full-set-acrylic-with-gel-color-powder')->firstOrFail();
        $retired = SalonService::create(['category_id' => $service->category_id, 'slug' => 'archived-service', 'name' => 'Archived service', 'sort_order' => 100, 'active' => false]);
        Livewire::test(ServiceMenu::class)
            ->set('categoryId', $service->category_id)->assertDontSee('Archived service')
            ->set('showHidden', true)->assertSee('Archived service');
        Livewire::test(ServiceMenu::class)
            ->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['fill_price' => 48])
            ->assertHasNoActionErrors();
        $this->assertSame('48.00', $service->fresh()->fill_price);
        $this->get('/services')->assertOk()->assertSee('$48+');
        Livewire::withQueryParams(['section' => 'services'])->test(WebsiteContent::class)
            ->fillForm(['profile.menu_brow_images' => ['menu-brow-3', 'menu-brow-1']])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame(['menu-brow-3', 'menu-brow-1'], SalonProfile::findOrFail(1)->data['menu_brow_images']);
        $this->get('/services')->assertSeeInOrder(['data-salon-image="menu-brow-3"', 'data-salon-image="menu-brow-1"'], false)->assertDontSee('data-salon-image="menu-brow-2"', false);
    }
}
