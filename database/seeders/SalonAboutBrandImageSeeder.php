<?php

namespace Database\Seeders;

use App\Models\SalonProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonAboutBrandImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            if (($profile->data['about_brand_image_revision'] ?? null) === '2026-10-30') {
                return;
            }

            $data = $profile->data;
            $data['about_brand_image'] ??= 'brand-logo';
            $data['about_brand_image_revision'] = '2026-10-30';
            $profile->update(['data' => $data]);
        });
    }
}
