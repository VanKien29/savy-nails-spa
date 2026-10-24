<?php

namespace Database\Seeders;

use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class SalonMapSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SiteSettingsService::class);
        if ($settings->get('maps_embed_url') || $settings->get('maps_url') !== 'https://maps.app.goo.gl/rZcnSx2muwHrotw88') {
            return;
        }

        // Copied from Share > Embed a map on the client's supplied Google Maps listing.
        $settings->update(array_replace($settings->all(), [
            'maps_embed_url' => "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3061.8632593665957!2d-78.96874629999999!3d35.0570522!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89ab157db2617489%3A0x73cbe5f46ddd362f!2sL'AMOUR%20NAILS%20-%20Fayetteville!5e1!3m2!1sen!2s!4v1792823068725!5m2!1sen!2s",
        ]));
    }
}
