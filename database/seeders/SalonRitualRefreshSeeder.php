<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonRitualRefreshSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SalonProfile::findOrFail(1);
        if (($profile->data['ritual_revision'] ?? null) === 'light-nails-v1') {
            return;
        }
        $keys = ['luxury-ritual-nude', 'luxury-ritual-pearl'];
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
        $manifest = collect(SalonCatalogData::luxuryImages())->keyBy('key');
        DB::transaction(function () use ($profile, $keys, $report, $manifest) {
            foreach ($keys as $index => $key) {
                $row = $manifest->get($key);
                $entry = $report->first(fn ($item) => str_replace('\\', '/', $item['file']) === 'the-k/'.$row['source']);
                if (empty($entry['variants'])) {
                    throw new \RuntimeException('Run media:optimize for '.$row['source']);
                }
                $variants = array_map(fn ($item) => array_replace($item, ['file' => str_replace('\\', '/', $item['file'])]), $entry['variants']);
                SalonImage::updateOrCreate(['key' => $key], ['alt' => $row['alt'], 'caption' => $row['caption'], 'variants' => $variants, 'category' => 'nails', 'is_inspiration' => true, 'show_in_gallery' => false, 'sort_order' => 100 + $index, 'source_url' => null, 'source_credit' => 'AI-created editorial inspiration']);
            }
            $home = Page::where('template', 'salon-home')->firstOrFail();
            $sections = $home->sections;
            foreach ($sections as &$section) {
                if (($section['id'] ?? null) !== 'ritual') {
                    continue;
                }
                foreach ($keys as $index => $key) {
                    if (isset($section['items'][$index])) {
                        $section['items'][$index]['label'] = $key;
                    }
                }
            }
            unset($section);
            $home->update(['sections' => $sections]);
            $data = $profile->data;
            $data['ritual_image'] = $keys[0];
            $data['ritual_revision'] = 'light-nails-v1';
            $profile->update(['data' => $data]);
        });
    }
}
