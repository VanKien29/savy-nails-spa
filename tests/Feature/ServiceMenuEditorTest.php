<?php

namespace Tests\Feature;

use App\Filament\Pages\ServiceMenu;
use App\Models\SalonCategory;
use App\Models\SalonService;
use App\Models\User;
use Database\Seeders\NailSalonSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceMenuEditorTest extends TestCase
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

    public function test_one_visual_editor_replaces_both_old_screens(): void
    {
        $this->get('/admin/salon-services')->assertRedirect('/admin/service-menu');
        $this->get('/admin/salon-categories')->assertRedirect('/admin/service-menu');
        $this->get('/admin/service-menu')->assertOk()->assertSee('Menu dịch vụ')
            ->assertSee('menu-editor-preview', false)->assertSee('Brow Wax')
            ->assertDontSee('fi-ta-table', false);
        $nails = SalonCategory::where('slug', 'nails')->firstOrFail();
        Livewire::test(ServiceMenu::class)->set('categoryId', $nails->id)
            ->assertSee('Full Set')->assertSee('Fill-in')->assertSee('$45+')
            ->assertDontSee('Fresh Orange Pedicure');
    }

    public function test_categories_and_service_steps_can_be_edited_without_slugs_or_position_fields(): void
    {
        $component = Livewire::test(ServiceMenu::class);
        $component->callAction('createCategory', data: ['name' => 'New Spa Menu', 'description' => 'A new collection'])->assertHasNoActionErrors();
        $category = SalonCategory::where('name', 'New Spa Menu')->firstOrFail();
        $slug = $category->slug;
        $component->callAction('createService', data: ['name' => 'New Spa Ritual', 'category_id' => $category->id, 'price' => 75, 'active' => true, 'included_steps' => "Foot soak\n\nHot towels\n15-minute massage"])->assertHasNoActionErrors();
        $service = SalonService::where('name', 'New Spa Ritual')->firstOrFail();
        $this->assertSame(['Foot soak', 'Hot towels', '15-minute massage'], $service->includes);
        $component->callAction(TestAction::make('editCategory')->arguments(['id' => $category->id]), data: ['name' => 'Updated Spa Menu'])->assertHasNoActionErrors();
        $this->assertSame($slug, $category->fresh()->slug);
        $this->get('/services')->assertOk()->assertSee('Updated Spa Menu')->assertSee('Hot towels');
        $component->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['included_steps' => "Foot soak\nNew step"])->assertHasNoActionErrors();
        $this->assertSame(['Foot soak', 'New step'], $service->fresh()->includes);
        $component->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['included_steps' => ''])->assertHasNoActionErrors();
        $this->assertSame([], $service->fresh()->includes);
        $component->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['included_steps' => implode("\n", array_fill(0, 25, 'Too many steps'))])->assertHasActionErrors(['included_steps']);
    }

    public function test_visibility_and_order_change_the_client_menu(): void
    {
        $nails = SalonCategory::where('slug', 'nails')->firstOrFail();
        $first = SalonService::where('category_id', $nails->id)->where('group', 'Nail Enhancements')->orderBy('sort_order')->firstOrFail();
        $next = SalonService::where('category_id', $nails->id)->where('group', $first->group)->whereKeyNot($first->id)->orderBy('sort_order')->firstOrFail();
        $component = Livewire::test(ServiceMenu::class)->set('categoryId', $nails->id);
        $component->call('moveService', $next->id, 'up');
        $this->get('/services')->assertSeeInOrder([$next->name, $first->name]);
        $component->call('toggleService', $first->id);
        $this->assertFalse($first->fresh()->active);
        $this->get('/services')->assertDontSee($first->name);
        $component->assertDontSee($first->name)->set('showHidden', true)->assertSee($first->name);
        $component->call('toggleService', $first->id);
        $this->get('/services')->assertSee($first->name);
        $component->call('moveCategory', $nails->id, 'up');
        $this->assertSame('nails', SalonCategory::orderBy('sort_order')->firstOrFail()->slug);
        $this->get('/services')->assertSeeInOrder(['Nail Services', 'Brows &amp; Lashes'], false);
        $component->call('moveService', $first->id, 'invalid')->assertStatus(422);
    }

    public function test_empty_category_can_be_deleted_and_populated_category_is_protected(): void
    {
        $component = Livewire::test(ServiceMenu::class);
        $category = SalonCategory::where('slug', 'nails')->firstOrFail();
        $component->callAction(TestAction::make('deleteCategory')->arguments(['id' => $category->id]))->assertHasErrors(['category']);
        $this->assertDatabaseHas('salon_categories', ['id' => $category->id]);
        $component->unmountAction();
        $component->callAction('createCategory', data: ['name' => 'Empty category'])->assertHasNoActionErrors();
        $empty = SalonCategory::where('name', 'Empty category')->firstOrFail();
        $component->callAction(TestAction::make('deleteCategory')->arguments(['id' => $empty->id]))->assertHasNoActionErrors();
        $this->assertDatabaseMissing('salon_categories', ['id' => $empty->id]);
    }

    public function test_non_admin_cannot_open_the_editor_or_mutate_services(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/service-menu')->assertForbidden();
        $service = SalonService::firstOrFail();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new ServiceMenu)->toggleService($service->id);
    }
}
