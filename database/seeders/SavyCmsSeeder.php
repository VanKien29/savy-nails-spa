<?php

namespace Database\Seeders;

use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Services\SalonContent;
use App\Services\SavyContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavyCmsSeeder extends Seeder
{
    public function run(): void
    {
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR))
            ->keyBy(fn ($entry) => str_replace('\\', '/', $entry['file']));
        DB::transaction(function () use ($report): void {
            foreach (config('savy.images') as $key => [$file, $alt]) {
                $variants = $report->get($file)['variants'] ?? [];
                if (! $variants) {
                    throw new \RuntimeException('Run npm run media:optimize. Missing image: '.$file);
                }
                SalonImage::firstOrCreate(['key' => $key], [
                    'alt' => $alt, 'caption' => $alt, 'category' => 'savy', 'sort_order' => 100,
                    'show_in_gallery' => false,
                    'variants' => array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $variants),
                ]);
            }
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            $data['savy'] = app(SavyContent::class)->profile($data);
            $profile->update(['data' => $data]);
        });
        app(SalonContent::class)->forget();
    }
}
