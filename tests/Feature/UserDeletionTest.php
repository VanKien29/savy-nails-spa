<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ManageUsers;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    public function test_admin_can_delete_another_account_and_revoke_access_without_losing_history(): void
    {
        config(['session.driver' => 'database']);
        $actor = $this->admin();
        $target = $this->admin();
        $this->actingAs($actor);
        DB::table('sessions')->insert(['id' => 'deleted-user-session', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $target->email, 'token' => 'expired-with-user', 'created_at' => now()]);
        $page = Page::create(['title' => 'Preserved page', 'slug' => 'preserved-page', 'template' => 'sections', 'status' => 'draft', 'sections' => []]);
        $revision = PageRevision::create(['page_id' => $page->id, 'user_id' => $target->id, 'snapshot' => ['title' => 'Earlier title']]);
        $audit = AuditLog::create(['user_id' => $target->id, 'model' => Page::class, 'record_id' => $page->id, 'action' => 'updated', 'changes' => ['fields' => ['title']]]);

        $this->assertTrue($actor->can('delete', $target));
        Livewire::test(ManageUsers::class)->callAction(TestAction::make('delete')->table($target))->assertHasNoActionErrors();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $target->email]);
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
        $this->assertNull($audit->fresh()->user_id);
        $this->assertNull($revision->fresh()->user_id);
        $this->assertSame(['title' => 'Earlier title'], $revision->fresh()->snapshot);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $actor->id, 'model' => User::class, 'record_id' => $target->id, 'action' => 'deleted']);
    }

    public function test_current_account_cannot_be_deleted_even_when_another_admin_exists(): void
    {
        $actor = $this->admin();
        $this->admin();
        $this->actingAs($actor);
        $this->assertFalse(UserResource::canDelete($actor));
        $this->assertFalse($actor->can('delete', $actor));
        Livewire::test(ManageUsers::class)->assertActionHidden(TestAction::make('delete')->table($actor));
        $this->expectException(HttpException::class);
        UserResource::deleteAccount($actor);
    }

    public function test_last_active_administrator_is_protected_at_model_level(): void
    {
        $actor = $this->admin();
        $inactive = $this->admin();
        $inactive->forceFill(['is_active' => false])->save();
        $this->expectException(ValidationException::class);
        $actor->delete();
    }

    public function test_public_accounts_cannot_call_account_deletion_directly(): void
    {
        $target = $this->admin();
        $this->actingAs(User::factory()->create());
        $this->expectException(HttpException::class);
        UserResource::deleteAccount($target);
    }

    public function test_site_settings_is_hidden_from_navigation_without_changing_saved_configuration(): void
    {
        $this->actingAs($this->admin());
        $html = $this->get('/admin')->assertOk()->getContent();
        $this->assertStringNotContainsString('/admin/site-settings', $html);
        $this->assertFalse(SiteSettings::shouldRegisterNavigation());
        $menu = Filament::getPanel('admin')->getUserMenuItems();
        $this->assertArrayNotHasKey('profile', $menu);
        $this->assertArrayHasKey('logout', $menu);
        $this->get('/admin/site-settings')->assertOk();
    }
}
