<?php

namespace Tests\Feature;

use App\Filament\Pages\ScriptWidgets;
use App\Filament\Pages\SiteSettings;
use App\Models\Page;
use App\Models\User;
use App\Services\PageRenderer;
use App\Services\SiteSettingsService;
use Database\Seeders\NailSalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScriptWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private const HEAD = '<script id="custom-head-test">window.customHeadLoaded = true;</script>';

    private const BODY = '<div id="custom-widget-test">Custom widget</div><script>window.customBodyLoaded = true;</script>';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NailSalonSeeder::class);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'cms_role' => 'admin'])->save();
        $this->actingAs($admin);
    }

    private function saveCode(): void
    {
        Livewire::test(ScriptWidgets::class)->fillForm(['script_head' => self::HEAD, 'script_body_end' => self::BODY])->call('save')->assertHasNoFormErrors();
    }

    public function test_two_textareas_save_raw_code_and_render_in_the_correct_positions(): void
    {
        $response = $this->get('/admin/script-widgets')->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $this->assertCount(2, $dom->getElementsByTagName('textarea'));
        $this->saveCode();
        $this->assertSame(self::HEAD, app(SiteSettingsService::class)->get('script_head'));
        $this->assertSame(self::BODY, app(SiteSettingsService::class)->get('script_body_end'));
        $response = $this->get('/')->assertOk()->assertSee(self::HEAD, false)->assertSee(self::BODY, false);
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, '</head>'), strpos($html, self::HEAD));
        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, self::BODY));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, self::BODY));
        Page::create(['title' => 'Widget layout test', 'slug' => 'widget-layout-test', 'template' => 'sections', 'status' => 'published', 'sections' => []]);
        $this->get('/widget-layout-test')->assertOk()->assertSee(self::HEAD, false)->assertSee(self::BODY, false);
    }

    public function test_external_embeds_are_allowed_only_on_live_client_responses(): void
    {
        $this->saveCode();
        $this->assertStringContainsString("connect-src 'self' http: https: ws: wss:", $this->get('/')->headers->get('Content-Security-Policy'));
        $admin = $this->get('/admin/script-widgets')->assertOk()->assertDontSee(self::HEAD, false)->assertDontSee(self::BODY, false);
        $this->assertStringNotContainsString("connect-src 'self' http: https: ws: wss:", $admin->headers->get('Content-Security-Policy'));
        $preview = app(PageRenderer::class)->render(Page::where('is_home', true)->firstOrFail(), true)->render();
        $this->assertStringNotContainsString(self::HEAD, $preview);
        $this->assertStringNotContainsString(self::BODY, $preview);
        config(['starter.exporting' => true]);
        $this->get('/')->assertOk()->assertDontSee(self::HEAD, false)->assertDontSee(self::BODY, false);
    }

    public function test_clearing_both_fields_removes_code_and_restores_default_policy(): void
    {
        $this->saveCode();
        Livewire::test(ScriptWidgets::class)->fillForm(['script_head' => null, 'script_body_end' => ''])->call('save')->assertHasNoFormErrors();
        $response = $this->get('/')->assertOk()->assertDontSee(self::HEAD, false)->assertDontSee(self::BODY, false);
        $this->assertStringNotContainsString("connect-src 'self' http: https: ws: wss:", $response->headers->get('Content-Security-Policy'));
    }

    public function test_saving_general_settings_preserves_custom_code(): void
    {
        $this->saveCode();
        Livewire::test(SiteSettings::class)->fillForm(['site_name' => 'A new website name'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(self::HEAD, app(SiteSettingsService::class)->get('script_head'));
        $this->assertSame(self::BODY, app(SiteSettingsService::class)->get('script_body_end'));
    }

    public function test_existing_admin_accounts_can_save_code_regardless_of_legacy_role(): void
    {
        foreach (['editor', 'support'] as $role) {
            $user = User::factory()->create();
            $user->forceFill(['is_admin' => true, 'cms_role' => $role])->save();
            $this->actingAs($user);
            $this->get('/admin/script-widgets')->assertOk();
            $this->saveCode();
        }
    }

    public function test_public_and_disabled_accounts_cannot_open_or_submit_the_code_editor(): void
    {
        foreach ([false, true] as $isAdmin) {
            $user = User::factory()->create();
            $user->forceFill(['is_admin' => $isAdmin, 'is_active' => ! $isAdmin])->save();
            $this->actingAs($user);
            $this->get('/admin/script-widgets')->assertForbidden();
            Livewire::test(ScriptWidgets::class)->assertForbidden();
        }
    }
}
