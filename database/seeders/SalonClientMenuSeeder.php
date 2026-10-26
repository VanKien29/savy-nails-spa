<?php

namespace Database\Seeders;

use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Services\SalonContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonClientMenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            $menu = ClientMenuData::document();
            if (($data['menu_revision'] ?? null) === $menu['revision']) {
                return;
            }

            $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
            foreach (ClientMenuData::images() as $index => $image) {
                $entry = $report->first(fn ($row) => str_replace('\\', '/', $row['file']) === 'the-k/'.$image['source']);
                if (empty($entry['variants'])) {
                    throw new \RuntimeException('Run npm run media:optimize before applying the client service menu.');
                }
                SalonImage::firstOrCreate(['key' => $image['key']], [
                    'caption' => $image['caption'], 'alt' => $image['alt'],
                    'variants' => array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $entry['variants']),
                    'category' => 'beauty', 'is_inspiration' => false, 'show_in_gallery' => false,
                    'sort_order' => 310 + $index, 'source_credit' => 'Client-provided service menu',
                ]);
            }

            foreach ($menu['categories'] as $index => $category) {
                SalonCategory::where('slug', $category['slug'])->firstOrFail()->update(['name' => $category['name'], 'sort_order' => $index]);
            }
            $categories = SalonCategory::all()->keyBy('slug');
            foreach ($menu['services'] as $index => $row) {
                $fields = array_replace([
                    'description' => null, 'group' => null, 'price' => null, 'gel_price' => null, 'fill_price' => null,
                    'price_from' => false, 'price_note' => null, 'duration_label' => null, 'massage_minutes' => null,
                    'includes' => [], 'includes_label' => null,
                ], collect($row)->except(['slug', 'category'])->all(), [
                    'category_id' => $categories[$row['category']]->id, 'sort_order' => $index, 'active' => true,
                ]);
                SalonService::updateOrCreate(['slug' => $row['slug']], $fields);
            }
            // Preserve historical IDs and inquiry references for services retired from this menu.
            SalonService::whereNotIn('slug', array_column($menu['services'], 'slug'))->update(['active' => false]);
            $data['menu_revision'] = $menu['revision'];
            $data['menu_brow_images'] = array_column(ClientMenuData::images(), 'key');
            $data['copy']['menu']['kids_note'] = '(For children who are under 12 years old)';
            $data['copy']['menu']['gel_label'] = 'Gel Polish';
            $data['copy']['menu']['includes_label'] = 'Includes:';
            $profile->update(['data' => $data]);
        });
        app(SalonContent::class)->forget();
    }
}
