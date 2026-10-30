<?php

namespace Database\Seeders;

use App\Models\SalonProfile;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SalonPearlLogoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            if (($profile->data['pearl_logo_revision'] ?? null) === '2026-10-30') {
                return;
            }

            $queued = config('starter.media.queue');
            config(['starter.media.queue' => false]);
            try {
                $logo = app(MediaService::class)->upload(new UploadedFile(
                    resource_path('media/the-k/site-logo-pearl-20261030.png'),
                    'the-k-pearl-logo-transparent.png', 'image/png', null, true,
                ), 'THE K NAIL BAR — Nails, Lashes, Brows');
            } finally {
                config(['starter.media.queue' => $queued]);
            }

            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), ['logo_id' => $logo->id, 'mobile_logo_id' => $logo->id]));
            $profile->update(['data' => array_replace($profile->data, ['pearl_logo_revision' => '2026-10-30'])]);
        });
    }
}
