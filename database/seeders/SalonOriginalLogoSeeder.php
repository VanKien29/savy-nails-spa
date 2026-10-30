<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\SalonProfile;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SalonOriginalLogoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            if (($profile->data['original_logo_restore_revision'] ?? null) === '2026-10-30') {
                return;
            }

            $logo = Media::where('name', 'the-k-client-logo-transparent.png')->latest('id')->first();
            if (! $logo) {
                $queued = config('starter.media.queue');
                config(['starter.media.queue' => false]);
                try {
                    $logo = app(MediaService::class)->upload(new UploadedFile(
                        resource_path('media/the-k/site-logo-client-v2.png'),
                        'the-k-client-logo-transparent.png',
                        'image/png',
                        null,
                        true,
                    ), 'THE K LUXURY NAIL BAR logo');
                } finally {
                    config(['starter.media.queue' => $queued]);
                }
            }

            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), [
                'logo_id' => $logo->id,
                'mobile_logo_id' => $logo->id,
            ]));
            $profile->update(['data' => array_replace($profile->data, [
                'original_logo_restore_revision' => '2026-10-30',
            ])]);
        });
    }
}
