<?php

namespace Database\Seeders;

use App\Models\SalonProfile;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SalonBrandSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SalonProfile::find(1);
        if (! $profile || ($profile->data['brand_revision'] ?? null) === 'image-logo-v1') {
            return;
        }
        DB::transaction(function () use ($profile) {
            $settings = app(SiteSettingsService::class);
            $data = $settings->all();
            if (! $data['logo_id'] && ! $data['mobile_logo_id']) {
                $queued = config('starter.media.queue');
                config(['starter.media.queue' => false]);
                try {
                    $logo = app(MediaService::class)->upload(new UploadedFile(resource_path('media/the-k/site-logo-v1.png'), 'the-k-site-logo-v1.png', 'image/png', null, true), 'THE K LUXURY NAIL BAR logo');
                } finally {
                    config(['starter.media.queue' => $queued]);
                }
                $data['logo_id'] = $logo->id;
                $data['mobile_logo_id'] = $logo->id;
                $settings->update($data);
            }
            $profile->update(['data' => array_replace($profile->data, ['brand_revision' => 'image-logo-v1'])]);
        });
    }
}
