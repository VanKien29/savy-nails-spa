<?php

namespace Tests\Feature;

use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\ServiceMenu;
use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ManageUsers;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\CmsAccess;
use App\Services\MediaService;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyNailsSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CmsManagementTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role = 'admin', bool $active = true): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true, 'is_active' => $active, 'cms_role' => $role])->save();

        return $user;
    }

    private function salon(): void
    {
        $this->seed(SavyNailsSeeder::class);
        app(SalonContent::class)->forget();
    }

    public function test_every_management_screen_renders_for_admin(): void
    {
        $this->salon();
        $this->actingAs($this->account());
        foreach (['/admin', '/admin/service-menu', '/admin/website-content', '/admin/users', '/admin/site-settings', '/admin/profile'] as $path) {
            $this->get($path)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_existing_admin_accounts_have_full_access_regardless_of_legacy_role(): void
    {
        foreach (['admin', 'editor', 'support', 'legacy-role'] as $role) {
            $user = $this->account($role);
            $this->actingAs($user);
            $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
            foreach (['content', 'profile', 'users', 'settings', 'audit'] as $area) {
                $this->assertTrue(CmsAccess::allows($area));
            }
            foreach (['service-menu', 'website-content', 'users', 'site-settings', 'media-library', 'audit-logs'] as $path) {
                $this->get('/admin/'.$path)->assertOk();
            }
            $this->assertTrue(SiteSettings::canAccess());
        }
    }

    public function test_retired_admin_modules_are_not_exposed_even_to_administrators(): void
    {
        $this->actingAs($this->account());
        foreach (['/admin/salon-settings', '/admin/leads', '/admin/media', '/admin/media/create', '/admin/navigation-items', '/admin/redirects'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_disabled_or_public_accounts_cannot_enter_panel(): void
    {
        $this->actingAs($this->account('editor', false))->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_service_crud_runs_through_livewire_and_updates_client(): void
    {
        $this->salon();
        $this->actingAs($this->account('editor'));
        $category = SalonCategory::first();
        $component = Livewire::test(ServiceMenu::class);
        $component->callAction('createService', data: ['name' => 'CMS Test Treatment', 'slug' => 'cms-test-treatment', 'category_id' => $category->id, 'price' => 47.50, 'sort_order' => 8, 'active' => true, 'featured' => false, 'price_from' => false])->assertHasNoActionErrors();
        $service = SalonService::where('name', 'CMS Test Treatment')->firstOrFail();
        app(SalonContent::class)->forget();
        $this->get('/services')->assertOk()->assertSee('CMS Test Treatment');
        $this->get('/services')->assertSee('$47.50');
        $component->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['name' => 'CMS Updated Treatment', 'price' => 55])->assertHasNoActionErrors();
        $this->assertSame('55.00', $service->fresh()->price);
        $component->callAction(TestAction::make('deleteService')->arguments(['id' => $service->id]))->assertHasNoActionErrors();
        $this->assertDatabaseMissing('salon_services', ['id' => $service->id]);
        $this->assertTrue(AuditLog::where('model', SalonService::class)->where('record_id', $service->id)->where('action', 'deleted')->exists(), AuditLog::where('model', SalonService::class)->latest('id')->limit(5)->get()->toJson());
    }

    public function test_menu_edits_preserve_retired_metadata_and_update_the_current_layout(): void
    {
        $this->salon();
        $this->actingAs($this->account());
        $service = SalonService::firstOrFail();
        $service->update(['massage_minutes' => 15]);
        $metadata = $service->only(['image_id', 'massage_minutes', 'featured']);
        Livewire::test(ServiceMenu::class)
            ->callAction(TestAction::make('editService')->arguments(['id' => $service->id]), data: ['name' => 'Updated classic menu service'])
            ->assertHasNoActionErrors();
        $this->assertSame($metadata, $service->fresh()->only(['image_id', 'massage_minutes', 'featured']));
        $category = $service->category;
        $imageId = $category->image_id;
        Livewire::test(ServiceMenu::class)
            ->callAction(TestAction::make('editCategory')->arguments(['id' => $category->id]), data: ['name' => 'Updated classic menu category'])
            ->assertHasNoActionErrors();
        $this->assertSame($imageId, $category->fresh()->image_id);
        $this->get('/services')->assertOk()->assertSee('Updated classic menu service')->assertSee('Updated classic menu category');
        $this->get('/')->assertOk()->assertSee('Updated classic menu category');
    }

    public function test_footer_contact_changes_are_saved_and_html_is_escaped(): void
    {
        $this->salon();
        $this->actingAs($this->account('editor'));
        Livewire::test(FooterSettings::class)->fillForm(['settings.address' => '<script>alert(1)</script>'])->call('save')->assertHasNoFormErrors();
        app(SalonContent::class)->forget();
        $this->get('/')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_copy_links_cannot_inject_script_urls(): void
    {
        $this->expectException(ValidationException::class);
        SalonProfile::create(['id' => 1, 'data' => ['copy' => ['global' => ['booking_url' => 'javascript:alert(1)']]]]);
    }

    public function test_uploaded_image_can_replace_any_salon_slot_and_is_protected(): void
    {
        $this->salon();
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('replacement.jpg', 900, 600), 'Replacement');
        $image = SalonImage::where('key', SalonProfile::find(1)->data['story_image'])->firstOrFail();
        $image->update(['media_id' => $media->id]);
        app(SalonContent::class)->forget();
        $this->get('/')->assertOk()->assertSee($media->url(), false);
        $this->assertTrue(app(MediaService::class)->inUse($media));
        $this->expectException(ValidationException::class);
        $image->delete();
    }

    public function test_category_with_services_cannot_be_deleted(): void
    {
        $category = SalonCategory::create(['name' => 'Test', 'slug' => 'test', 'description' => '', 'sort_order' => 0]);
        SalonService::create(['name' => 'Treatment', 'slug' => 'treatment', 'category_id' => $category->id, 'sort_order' => 0]);
        $this->expectException(ValidationException::class);
        $category->delete();
    }

    public function test_page_revision_can_be_restored_without_losing_current_version(): void
    {
        $this->actingAs($this->account('editor'));
        $page = Page::create(['title' => 'Original', 'slug' => 'original', 'template' => 'sections', 'status' => 'draft', 'sections' => []]);
        $page->update(['title' => 'Updated']);
        $revision = PageRevision::where('page_id', $page->id)->firstOrFail();
        $this->assertSame('Original', $revision->snapshot['title']);
        $page->update($revision->snapshot);
        $this->assertSame('Original', $page->fresh()->title);
        $this->assertSame('Updated', PageRevision::where('page_id', $page->id)->latest('id')->first()->snapshot['title']);
    }

    public function test_obsolete_home_blocks_cannot_restore_the_retired_layout(): void
    {
        $this->salon();
        $page = Page::where('is_home', true)->firstOrFail();
        $sections = collect($page->sections)->map(function ($section) {
            $section['ordering'] = $section['id'] === 'journey' ? 0 : 10;
            if ($section['id'] === 'collection') {
                $section['enabled'] = false;
            }

            return $section;
        })->all();
        $page->update(['sections' => $sections]);
        $this->get('/')->assertOk()->assertSee('av-hero', false)->assertSee('Your Savy Moment')
            ->assertDontSee('kl-collection-grid', false)->assertDontSee('data-hero-slideshow', false);
    }

    public function test_last_administrator_cannot_be_disabled(): void
    {
        $admin = $this->account();
        $this->actingAs($admin);
        $this->expectException(ValidationException::class);
        UserResource::persist(['is_active' => false], $admin);
    }

    public function test_public_account_cannot_call_user_mutation_directly(): void
    {
        $this->actingAs(User::factory()->create());
        $this->expectException(HttpException::class);
        UserResource::persist(['name' => 'Intruder', 'email' => 'intruder@example.com', 'password' => 'password']);
    }

    public function test_admin_accounts_are_managed_without_role_fields(): void
    {
        $admin = $this->account('support');
        $this->actingAs($admin);
        $this->get('/admin/users')->assertOk()->assertDontSee('Vai trò');
        $component = Livewire::test(ManageUsers::class)->assertTableColumnDoesNotExist('cms_role');
        $component->callAction('create', data: [
            'name' => 'New Admin', 'email' => 'new-admin@example.com',
            'password' => 'NewAdminPassword123!', 'is_active' => true,
        ])->assertHasNoActionErrors();
        $newAdmin = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertTrue($newAdmin->is_admin);
        $this->actingAs($newAdmin);
        $this->assertTrue(CmsAccess::allows('users'));
        $this->assertTrue(CmsAccess::allows('settings'));
        UserResource::persist(['is_active' => false], $admin);
        $this->assertFalse($admin->fresh()->is_active);
    }

    public function test_admin_has_no_two_factor_authentication(): void
    {
        $user = $this->account();
        $this->actingAs($user);
        $panel = Filament::getPanel('admin');
        $this->assertFalse($panel->hasMultiFactorAuthentication());
        $this->assertFalse($panel->isMultiFactorAuthenticationRequired());
        $this->get('/admin/users')->assertOk()->assertDontSee('2FA');
        $this->get('/admin/profile')->assertOk()->assertDontSee('setUpAppAuthentication');
    }

    public function test_legacy_authentication_secrets_remain_hidden(): void
    {
        $user = $this->account();
        $user->forceFill([
            'app_authentication_secret' => encrypt('test-secret'),
            'app_authentication_recovery_codes' => encrypt(['recovery-one']),
        ])->save();
        $this->assertArrayNotHasKey('app_authentication_secret', $user->toArray());
        $this->assertArrayNotHasKey('app_authentication_recovery_codes', $user->toArray());
        $this->assertStringNotContainsString('test-secret', AuditLog::all()->toJson());
    }

    public function test_clearing_social_links_does_not_restore_old_entries_from_defaults(): void
    {
        $service = app(SiteSettingsService::class);
        $service->update(array_replace(SiteSettingsService::defaults(), ['social_links' => [['label' => 'Instagram', 'url' => 'https://instagram.com/example']]]));
        $service->update(array_replace($service->all(), ['social_links' => []]));
        $this->assertSame([], $service->all()['social_links']);
    }

    public function test_new_pages_use_the_salon_layout_and_safe_flexible_sections(): void
    {
        $this->salon();
        Page::create(['title' => 'Our New Collection', 'slug' => 'new-collection', 'template' => 'salon-content', 'status' => 'published', 'sections' => [
            ['id' => 'welcome', 'type' => 'about', 'variant' => 'centered', 'enabled' => true, 'heading' => 'A new chapter', 'body' => 'Made for you', 'button_label' => 'Explore', 'button_url' => '/services'],
        ]]);
        $this->get('/new-collection')->assertOk()->assertSee('av-header', false)->assertSee('A new chapter')->assertSee('Made for you')->assertSee('Explore');
    }
}
