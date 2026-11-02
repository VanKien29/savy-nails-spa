<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Page;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class SavySocialImageSeeder extends Seeder
{
    public function run(): void
    {
        $name = 'savy-social-gold-manicure-v2.png';
        $image = Media::where('name', $name)->where('status', 'ready')->first();
        if (! $image) {
            $image = app(MediaService::class)->upload(
                new UploadedFile(resource_path('media/the-k/hero-gold-nail-oil.png'), $name, 'image/png', null, true),
                'Savy Nails & Spa — nude manicure with fine gold details and golden nail oil',
            );
        }

        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace($settings->all(), ['og_media_id' => $image->id]));
        foreach (Page::whereIn('slug', ['home', 'about-us', 'services', 'gallery', 'contact-us'])->get() as $page) {
            $page->update(['og_media_id' => $image->id]);
        }
    }
}
