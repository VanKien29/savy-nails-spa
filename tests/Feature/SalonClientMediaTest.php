<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\SalonImage;
use App\Services\SiteSettingsService;
use Database\Seeders\NailSalonSeeder;
use Database\Seeders\SalonClientMediaSeeder;
use Database\Seeders\SalonOriginalLogoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SalonClientMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_logo_and_gallery_use_saved_settings_and_preserve_later_edits(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        config(['starter.preview' => false, 'app.locale' => 'en']);
        $this->seed(NailSalonSeeder::class);

        $settings = app(SiteSettingsService::class);
        $logo = Media::findOrFail($settings->get('logo_id'));
        $this->assertSame('the-k-client-logo-transparent.png', $logo->name);
        $this->assertSame($logo->id, $settings->get('mobile_logo_id'));
        $this->assertSame(6, SalonImage::where('show_in_gallery', true)->count());

        $original = imagecreatefrompng(Storage::disk('local')->path($logo->original_path));
        $this->assertSame(127, imagecolorsforindex($original, imagecolorat($original, 0, 0))['alpha']);
        $this->get('/gallery')->assertOk()->assertSee('ks-gallery-masonry')->assertSee('pinterest-long-french');

        $settings->update(array_replace($settings->all(), ['mobile_logo_id' => null]));
        SalonImage::where('key', 'pinterest-burgundy-french')->firstOrFail()->update(['show_in_gallery' => false]);
        $this->seed(SalonClientMediaSeeder::class);
        $mediaCount = Media::count();
        $this->seed(SalonOriginalLogoSeeder::class);
        $this->assertSame($mediaCount, Media::count());
        $this->assertSame($logo->id, $settings->get('logo_id'));
        $this->assertNull($settings->get('mobile_logo_id'));
        $this->assertSame(5, SalonImage::where('show_in_gallery', true)->count());
    }
}
