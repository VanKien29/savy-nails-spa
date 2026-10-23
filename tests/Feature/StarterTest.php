<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\LeadResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\User;
use App\Services\LeadCsvExporter;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StarterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        config(['starter.site_url' => 'https://production.example', 'starter.preview' => false]);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_replace(['full_name' => 'Nguyễn Văn An', 'phone' => '0901234567', 'email' => 'an@example.com', 'message' => 'Tư vấn dự án website', 'page_id' => Page::where('is_home', true)->value('id'), 'form_token' => Crypt::encryptString((string) (time() - 5)), 'consent' => '1', 'website' => '', 'utm' => ['source' => 'test']], $override);
    }

    public function test_public_pages_render_shared_seo_and_semantic_html(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('Thương hiệu tốt.')->assertSee('https://production.example/', false)->assertSee('application/ld+json', false)->assertSee('og:title', false)->assertSee('twitter:card', false);
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $this->assertCount(1, $dom->getElementsByTagName('h1'));
        foreach ($dom->getElementsByTagName('img') as $image) {
            $this->assertTrue($image->hasAttribute('alt'));
        }
        $this->get('/dich-vu/thiet-ke')->assertOk()->assertSee('Thiết kế để tạo kết nối.');
    }

    public function test_sitemap_and_robots_use_only_production_indexable_pages(): void
    {
        Page::create(['title' => 'Draft', 'slug' => 'draft-page', 'status' => 'draft', 'template' => 'sections']);
        Page::create(['title' => 'Private', 'slug' => 'hidden-page', 'status' => 'published', 'noindex' => true, 'template' => 'sections']);
        Page::create(['title' => 'Future', 'slug' => 'future-page', 'status' => 'published', 'published_at' => now()->addDay(), 'template' => 'sections']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://production.example/dich-vu/thiet-ke', false)->assertDontSee('draft-page')->assertDontSee('hidden-page')->assertDontSee('future-page')->assertDontSee('localhost');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin', false);
        $this->get('/draft-page')->assertNotFound();
        $this->get('/future-page')->assertNotFound();
        config(['starter.preview' => true]);
        $this->get('/')->assertOk()->assertSee('noindex, nofollow', false)->assertDontSee('name="form_token"', false)->assertSee('Form trình diễn');
        $this->get('/sitemap.xml')->assertDontSee('<loc>', false);
        $this->get('/robots.txt')->assertSee('Disallow: /', false);
    }

    public function test_slug_change_creates_301_and_reversal_does_not_loop(): void
    {
        $page = Page::where('slug', 'dich-vu/thiet-ke')->first();
        $page->update(['slug' => 'giai-phap']);
        $this->get('/dich-vu/thiet-ke')->assertRedirect('/giai-phap')->assertStatus(301);
        $page->update(['slug' => 'dich-vu/thiet-ke']);
        $this->get('/giai-phap')->assertStatus(301)->assertRedirect('/dich-vu/thiet-ke');
        $this->get('/dich-vu/thiet-ke')->assertOk();
    }

    public function test_redirect_cycles_are_rejected(): void
    {
        Redirect::create(['source' => '/one', 'target' => '/two']);
        $this->expectException(ValidationException::class);
        Redirect::create(['source' => '/two', 'target' => '/one']);
    }

    public function test_home_internal_slug_change_and_home_handover_keep_correct_urls(): void
    {
        $home = Page::where('is_home', true)->first();
        $home->update(['slug' => 'home-internal']);
        $this->get('/')->assertOk();
        $home->update(['is_home' => false]);
        $next = Page::where('slug', 'dich-vu/thiet-ke')->first();
        $next->update(['is_home' => true]);
        $this->get('/')->assertOk()->assertSee('Thiết kế để tạo kết nối.');
        $this->assertFalse(Redirect::where('source', '/')->exists());
    }

    public function test_lead_persists_utm_and_is_deduplicated(): void
    {
        $this->postJson('/contact', $this->payload())->assertCreated();
        $this->postJson('/contact', $this->payload())->assertCreated();
        $this->assertDatabaseCount('leads', 1);
        $this->assertSame(['source' => 'test'], Lead::first()->utm);
    }

    public function test_invalid_spam_consent_and_extra_fields_are_rejected(): void
    {
        $this->postJson('/contact', $this->payload(['phone' => 'bad', 'email' => null, 'consent' => null]))->assertUnprocessable()->assertJsonValidationErrors(['phone', 'consent']);
        $this->postJson('/contact', $this->payload(['website' => 'bot.example']))->assertUnprocessable();
        $this->postJson('/contact', $this->payload(['form_token' => Crypt::encryptString((string) time())]))->assertUnprocessable();
        $this->postJson('/contact', $this->payload(['extra' => ['unapproved' => 'value']]))->assertUnprocessable();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_rate_limit_and_preview_reject_writes(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/contact', $this->payload())->assertCreated();
        }
        $this->postJson('/contact', $this->payload())->assertStatus(429);
        Cache::flush();
        config(['starter.preview' => true]);
        $this->postJson('/contact', $this->payload())->assertForbidden();
    }

    public function test_mail_failure_does_not_lose_lead_and_captcha_fails_closed(): void
    {
        config(['starter.leads.notification_email' => 'admin@example.com']);
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('Mail unavailable'));
        $this->postJson('/contact', $this->payload())->assertCreated();
        $this->assertDatabaseCount('leads', 1);
        config(['starter.leads.captcha_secret' => 'test']);
        Http::fake(['*' => Http::response(['success' => false])]);
        $this->postJson('/contact', $this->payload(['message' => 'Another']))->assertUnprocessable()->assertJsonValidationErrors('captcha');
    }

    public function test_settings_cache_is_invalidated_and_tokens_are_validated(): void
    {
        $service = app(SiteSettingsService::class);
        $service->all();
        $this->assertTrue(Cache::has('starter.settings'));
        $data = $service->all();
        $data['site_name'] = 'New brand';
        $service->update($data);
        $this->assertSame('New brand', $service->get('site_name'));
        $this->get('/')->assertSee('New brand');
        $data['primary_color'] = 'red; background:url(evil)';
        $this->expectException(ValidationException::class);
        $service->update($data);
    }

    public function test_admin_resources_and_settings_require_authorized_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->assertFalse(PageResource::canViewAny());
        $this->assertFalse(SiteSettings::canAccess());
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/pages', '/admin/pages/create', '/admin/leads', '/admin/media', '/admin/media/create', '/admin/navigation-items', '/admin/redirects', '/admin/audit-logs', '/admin/site-settings', '/admin/profile'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->assertTrue(LeadResource::canViewAny());
    }

    public function test_admin_livewire_page_crud_and_settings_persist(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(CreatePage::class)->fillForm(['title' => 'New page', 'slug' => 'new-page', 'template' => 'sections', 'status' => 'draft', 'sections' => [], 'is_home' => false, 'noindex' => false, 'exportable' => false])->call('create')->assertHasNoFormErrors();
        $page = Page::where('slug', 'new-page')->firstOrFail();
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])->fillForm(['title' => 'Edited page'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Edited page', $page->fresh()->title);
        Livewire::test(SiteSettings::class)->fillForm(['site_name' => 'Changed in admin'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Changed in admin', app(SiteSettingsService::class)->get('site_name'));
    }

    public function test_signed_preview_requires_session_and_is_noindex(): void
    {
        $page = Page::first();
        $url = URL::temporarySignedRoute('pages.preview', now()->addMinutes(5), ['page' => $page]);
        $this->get($url)->assertRedirect('/admin/login');
        $this->actingAs($this->admin())->get($url)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('noindex, nofollow', false);
    }

    public function test_media_upload_resize_metadata_and_in_use_guard(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('safe.png', 300, 200), 'Alt text');
        $this->assertSame('ready', $media->status);
        $this->assertCount(1, $media->variants);
        $this->assertSame(300, $media->variants[0]['width']);
        Storage::disk('public')->assertExists($media->variants[0]['path']);
        $page = Page::first();
        $page->update(['hero_media_id' => $media->id]);
        $this->expectException(ValidationException::class);
        app(MediaService::class)->delete($media);
    }

    public function test_script_and_svg_uploads_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(MediaService::class)->upload(UploadedFile::fake()->createWithContent('evil.svg', '<svg onload="alert(1)"></svg>'));
    }

    public function test_audit_has_actor_but_no_password_or_lead_personal_data(): void
    {
        $this->actingAs($this->admin());
        $this->postJson('/contact', $this->payload())->assertCreated();
        $json = AuditLog::where('model', Lead::class)->get()->toJson();
        $this->assertStringNotContainsString('an@example.com', $json);
        $this->assertStringNotContainsString('0901234567', $json);
        $this->assertNotNull(AuditLog::where('model', Lead::class)->first()->user_id);
        $this->assertSame("'=SUM(1,2)", LeadCsvExporter::safeCell('=SUM(1,2)'));
    }

    public function test_csrf_middleware_and_deployment_configuration_are_present(): void
    {
        $middleware = app('router')->gatherRouteMiddleware(app('router')->getRoutes()->getByName('leads.store'));
        $this->assertContains(ValidateCsrfToken::class, $middleware);
        $config = json_decode(file_get_contents(base_path('vercel.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('dist/preview', $config['outputDirectory']);
        $this->assertArrayNotHasKey('functions', $config);
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Cache-Control', 'no-store, private');
        $this->get('/starter/showcase')->assertNotFound();
    }

    public function test_setup_is_repeatable_without_replacing_existing_content(): void
    {
        $this->artisan('starter:setup', ['--demo' => true])->assertSuccessful();
        $this->artisan('starter:setup', ['--demo' => true])->assertSuccessful();
        $this->assertDatabaseCount('pages', 2);
        $this->assertDatabaseCount('navigation_items', 6);
    }
}
