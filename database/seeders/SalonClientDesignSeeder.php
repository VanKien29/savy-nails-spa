<?php

namespace Database\Seeders;

use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Services\SalonContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonClientDesignSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (! SalonImage::where('key', 'contact-welcome')->exists()) {
                $image = collect(SalonCatalogData::luxuryImages())->firstWhere('key', 'contact-welcome');
                $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
                $entry = $report->first(fn ($row) => str_replace('\\', '/', $row['file']) === 'the-k/'.$image['source']);
                if (empty($entry['variants'])) {
                    throw new \RuntimeException('Run npm run media:optimize before applying the client design.');
                }
                SalonImage::create([
                    'key' => $image['key'], 'caption' => $image['caption'], 'alt' => $image['alt'],
                    'variants' => array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $entry['variants']),
                    'category' => 'nails', 'is_inspiration' => true, 'show_in_gallery' => false,
                    'sort_order' => 300, 'source_credit' => 'AI-created editorial inspiration',
                ]);
            }

            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            if (($data['client_design_revision'] ?? null) === 'pdf-2026-10-v1') {
                return;
            }
            $data['contact_image'] ??= 'contact-welcome';
            if (empty($data['copy']['global']['booking_url']) || in_array($data['copy']['global']['booking_url'], ['/contact-us', '/booking'], true)) {
                $data['copy']['global']['booking_url'] = config('cms.copy.global.booking_url.1');
            }
            foreach (['contact', 'footer'] as $group) {
                if (empty($data['copy'][$group]['hours_label']) || $data['copy'][$group]['hours_label'] === 'Salon Hours') {
                    $data['copy'][$group]['hours_label'] = 'Business Hours';
                }
            }
            $data['client_design_revision'] = 'pdf-2026-10-v1';
            $profile->update(['data' => $data]);

            if ($kids = SalonCategory::where('slug', 'kids')->first()) {
                if ($kids->name === 'Little Luxuries') {
                    $kids->update(['name' => 'Kid Services']);
                }
                foreach (SalonService::where('category_id', $kids->id)->get() as $service) {
                    if (preg_match('/^For children under 12\.?$/iu', trim($service->description ?? ''))) {
                        $service->update(['description' => '']);
                    }
                }
            }
        });
        app(SalonContent::class)->forget();
    }
}
