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
                new UploadedFile(resource_path('media/savy/social-manicure.png'), $name, 'image/png', null, true),
                'Savy Nails & Spa — nude manicure with fine gold details and golden nail oil',
            );
        }

        $settings = app(SiteSettingsService::class);
        if (! Media::whereKey($settings->get('og_media_id'))->where('status', 'ready')->exists()) {
            $settings->update(array_replace($settings->all(), ['og_media_id' => $image->id]));
        }
        foreach (Page::with('ogMedia')->whereIn('slug', ['home', 'about-us', 'services', 'gallery', 'contact-us'])->get() as $page) {
            if ($page->ogMedia?->status !== 'ready') {
                $page->update(['og_media_id' => $image->id]);
            }
        }
    }
}
