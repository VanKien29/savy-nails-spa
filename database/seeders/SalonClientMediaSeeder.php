<?php

namespace Database\Seeders;

use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SalonClientMediaSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SalonProfile::findOrFail(1);
        if (($profile->data['client_media_revision'] ?? null) === 'logo-gallery-v2') {
            return;
        }

        $gallery = ['luxury-gallery-ribbon', 'luxury-gallery-pearl', 'pinterest-long-french', 'pinterest-pearl-chrome', 'pinterest-gold-coffin', 'pinterest-burgundy-french'];
        if (SalonImage::whereIn('key', $gallery)->count() !== count($gallery)) {
            throw new \RuntimeException('Seed the salon image collection before the client media revision.');
        }

        DB::transaction(function () use ($profile, $gallery): void {
            $queued = config('starter.media.queue');
            config(['starter.media.queue' => false]);
            try {
                $logo = app(MediaService::class)->upload(new UploadedFile(resource_path('media/the-k/site-logo-client-v2.png'), 'the-k-client-logo-transparent.png', 'image/png', null, true), 'THE K LUXURY NAIL BAR logo');
            } finally {
                config(['starter.media.queue' => $queued]);
            }
            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), ['logo_id' => $logo->id, 'mobile_logo_id' => $logo->id]));

            foreach (SalonImage::where('show_in_gallery', true)->get() as $image) {
                $image->update(['show_in_gallery' => false]);
            }
            foreach ($gallery as $index => $key) {
                SalonImage::where('key', $key)->firstOrFail()->update(['show_in_gallery' => true, 'sort_order' => 200 + $index]);
            }
            $profile->update(['data' => array_replace($profile->data, [
                'client_media_revision' => 'logo-gallery-v2',
                'gallery_note' => 'A curated collection of AI-created editorial concepts and credited nail inspiration. These images explore styles and finishes; they are not photographs of salon client work.',
            ])]);
        });
    }
}
