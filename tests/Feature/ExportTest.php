<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\MediaService;
use App\Services\StaticPreviewExporter;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_is_multi_route_noindex_and_contains_no_private_state(): void
    {
        $this->seed(DemoSeeder::class);
        config(['starter.site_url' => 'https://production.example']);
        session()->flashInput(['full_name' => 'Private flash input', 'message' => 'Private flash message']);
        Page::create(['title' => 'Private draft content', 'slug' => 'private-draft', 'status' => 'draft', 'exportable' => true, 'template' => 'sections']);
        $name = 'test-'.bin2hex(random_bytes(8));
        $directory = base_path('dist/'.$name);
        try {
            $paths = app(StaticPreviewExporter::class)->export($name);
            $this->assertSame(['/', '/dich-vu/thiet-ke'], $paths);
            $html = File::get($directory.'/index.html');
            $this->assertStringContainsString('noindex, nofollow', $html);
            $this->assertStringContainsString('https://production.example/', $html);
            $this->assertStringContainsString('Form trình diễn', $html);
            $this->assertStringNotContainsString('Private flash', $html);
            $this->assertStringNotContainsString('name="_token"', $html);
            $this->assertStringNotContainsString('name="form_token"', $html);
            $this->assertStringNotContainsString('data-tracking=', $html);
            $this->assertStringContainsString('href="/build/assets/', $html);
            preg_match_all('~(?:src|href)="(/build/[^" ]+)"~', $html, $assetMatches);
            foreach ($assetMatches[1] as $asset) {
                $this->assertFileExists($directory.$asset);
            }
            $this->assertFileExists($directory.'/dich-vu/thiet-ke/index.html');
            $this->assertDirectoryDoesNotExist($directory.'/private-draft');
            $this->assertDirectoryDoesNotExist($directory.'/admin');
            $this->assertFileDoesNotExist($directory.'/.env');
            $this->assertFileExists($directory.'/assets/studio-space.svg');
            $this->assertFileExists($directory.'/build/manifest.json');
            $this->assertFileExists($directory.'/favicon.svg');
            $this->assertGreaterThan(0, filesize($directory.'/favicon.ico'));
            $this->assertFileExists($directory.'/site.webmanifest');
            $this->assertSame("User-agent: *\nDisallow: /\n", File::get($directory.'/robots.txt'));
            app(StaticPreviewExporter::class)->export($name); // owned output can be safely regenerated
        } finally {
            if (is_dir($directory) && realpath(dirname($directory)) === realpath(base_path('dist'))) {
                File::deleteDirectory($directory);
            }
        }
    }

    public function test_export_rejects_path_traversal(): void
    {
        $this->seed(DemoSeeder::class);
        $this->expectException(\RuntimeException::class);
        app(StaticPreviewExporter::class)->export('../outside');
    }

    public function test_export_only_copies_referenced_variants_and_keeps_absolute_og_metadata(): void
    {
        $this->seed(DemoSeeder::class);
        config(['starter.site_url' => 'https://production.example']);
        Storage::fake('public');
        Storage::fake('local');
        $service = app(MediaService::class);
        $used = $service->upload(UploadedFile::fake()->image('used.png', 80, 60), 'Used');
        $unused = $service->upload(UploadedFile::fake()->image('unused.png', 40, 30), 'Unused');
        Page::where('is_home', true)->first()->update(['hero_media_id' => $used->id, 'og_media_id' => $used->id]);
        $name = 'test-'.bin2hex(random_bytes(8));
        $directory = base_path('dist/'.$name);
        try {
            app(StaticPreviewExporter::class)->export($name);
            $this->assertFileExists($directory.'/storage/'.$used->variants[0]['path']);
            $this->assertFileDoesNotExist($directory.'/storage/'.$unused->variants[0]['path']);
            $this->assertDirectoryDoesNotExist($directory.'/originals');
            $html = File::get($directory.'/index.html');
            $this->assertStringContainsString('property="og:image" content="https://production.example/storage/'.$used->variants[0]['path'].'"', $html);
            $this->assertStringContainsString('src="/storage/'.$used->variants[0]['path'].'"', $html);
        } finally {
            if (is_dir($directory) && realpath(dirname($directory)) === realpath(base_path('dist'))) {
                File::deleteDirectory($directory);
            }
        }
    }
}
