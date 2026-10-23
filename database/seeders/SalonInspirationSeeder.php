<?php

namespace Database\Seeders;

use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use Illuminate\Database\Seeder;

class SalonInspirationSeeder extends Seeder
{
    public function run(): void
    {
        if ((SalonProfile::find(1)?->data['design_revision'] ?? null) === 'compact-v2') {
            return;
        }
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
        $source = json_decode(file_get_contents(database_path('seeders/data/the-k-pinterest.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($source as $index => $row) {
            $entry = $report->first(fn ($entry) => str_replace('\\', '/', $entry['file']) === 'the-k/'.$row['source']);
            if (empty($entry['variants'])) {
                throw new \RuntimeException('Optimize Pinterest assets before seeding.');
            }
            $variants = array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $entry['variants']);
            SalonImage::updateOrCreate(['key' => $row['key']], array_replace(collect($row)->except(['source', 'key'])->all(), ['variants' => $variants, 'category' => 'nails', 'show_in_gallery' => true, 'is_inspiration' => true, 'sort_order' => $index]));
        }
        foreach (SalonImage::whereIn('key', ['hero-manicure', 'blush-manicure', 'burgundy-manicure'])->get() as $image) {
            $image->update(['show_in_gallery' => false]);
        }
        SalonCategory::where('slug', 'nails')->first()?->update(['image_id' => SalonImage::where('key', 'pinterest-long-french')->value('id')]);
        $profile = SalonProfile::findOrFail(1);
        $data = $profile->data;
        $data['hero_image'] = 'pinterest-long-french';
        $data['story_image'] = 'pinterest-pearl-chrome';
        $data['booking_image'] = 'pinterest-burgundy-french';
        $data['design_revision'] = 'compact-v2';
        $data['gallery_note'] = 'Long-nail inspiration curated from Pinterest, alongside our treatment collection. Inspiration images belong to their credited creators and do not represent salon client work.';
        $profile->update(['data' => $data]);
    }
}
