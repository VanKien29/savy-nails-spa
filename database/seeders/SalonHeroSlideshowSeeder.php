<?php

namespace Database\Seeders;

use App\Models\SalonImage;
use App\Models\SalonProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonHeroSlideshowSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SalonProfile::findOrFail(1);
        if (($profile->data['hero_slideshow_revision'] ?? null) === 'v3') {
            return;
        }

        $slides = [
            ['key' => 'hero-marble-editorial', 'source' => 'hero-marble-editorial.png', 'alt' => 'Blush almond French-tip nails with delicate gold accents on ivory marble.', 'caption' => 'The Marble Edit'],
            ['key' => 'hero-gold-nail-oil', 'source' => 'hero-gold-nail-oil.png', 'alt' => 'Long nude nails with fine gold details holding a frosted nail oil bottle.', 'caption' => 'The Golden Detail'],
            ['key' => 'hero-manicure-ritual', 'source' => 'hero-manicure-ritual.png', 'alt' => 'A nail artist finishing a glossy milky manicure on a soft ivory towel.', 'caption' => 'The Manicure Ritual'],
        ];
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));

        foreach ($slides as $slide) {
            $entry = $report->first(fn (array $item) => str_replace('\\', '/', $item['file'] ?? '') === 'the-k/'.$slide['source']);
            if (empty($entry['variants'])) {
                throw new \RuntimeException('Run media:optimize for '.$slide['source'].'.');
            }
        }

        DB::transaction(function () use ($profile, $slides, $report): void {
            foreach ($slides as $index => $slide) {
                $entry = $report->first(fn (array $item) => str_replace('\\', '/', $item['file'] ?? '') === 'the-k/'.$slide['source']);
                $variants = array_map(fn (array $variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $entry['variants']);
                SalonImage::updateOrCreate(['key' => $slide['key']], [
                    'alt' => $slide['alt'], 'caption' => $slide['caption'], 'variants' => $variants,
                    'category' => 'nails', 'is_inspiration' => true, 'show_in_gallery' => false,
                    'sort_order' => 12 + $index, 'source_url' => null, 'source_credit' => 'AI-created editorial inspiration',
                ]);
            }

            $data = $profile->data;
            $data['hero_slides'] = array_column($slides, 'key');
            $data['hero_image'] = $slides[0]['key'];
            $data['hero_mobile_image'] = $slides[0]['key'];
            $data['hero_slideshow_revision'] = 'v3';
            $profile->update(['data' => $data]);
        });
    }
}
