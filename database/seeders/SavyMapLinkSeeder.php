<?php

namespace Database\Seeders;

use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class SavyMapLinkSeeder extends Seeder
{
    public const MAP_URL = 'https://maps.google.com/maps?ll=37.723875,-121.941944&z=18&t=m&hl=vi&gl=US&mapclient=embed&cid=17343372594423126593';

    public const EMBED_URL = 'https://www.google.com/maps/embed?pb=!1m3!3m2!1m1!4s17343372594423126593';

    public function run(): void
    {
        $settings = app(SiteSettingsService::class);
        $current = $settings->all();
        $current['maps_url'] = self::MAP_URL;
        $current['maps_embed_url'] = self::EMBED_URL;
        $current['social_links'] = array_map(function (array $social): array {
            if (strtolower($social['label']) === 'google') {
                $social['url'] = self::MAP_URL;
            }

            return $social;
        }, $current['social_links']);
        $settings->update($current);
    }
}
