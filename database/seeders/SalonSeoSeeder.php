<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalonSeoSeeder extends Seeder
{
    public function run(): void
    {
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR))
            ->keyBy(fn ($entry) => str_replace('\\', '/', $entry['file']));
        foreach (SalonImage::all() as $image) {
            $source = preg_replace('~-\d+\.(webp|avif)$~', '', $image->variants[0]['file'] ?? '');
            $variants = $report->get($source)['variants'] ?? [];
            $variants = array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $variants);
            if ($variants && $image->variants !== $variants) {
                $image->update(['variants' => $variants]);
            }
        }
        DB::transaction(function () {
            $profile = SalonProfile::find(1);
            if (! $profile || ($profile->data['seo_revision'] ?? null) === 'technical-v1') {
                return;
            }
            $pages = [
                'home' => ['Luxury Nails & Spa Pedicures in Fayetteville, NC | THE K', 'Explore manicures, spa pedicures, lashes and brows at THE K Luxury Nail Bar in Fayetteville, NC. View our treatment menu and contact the salon.', 'Nails, lashes, brows — and a little time that belongs entirely to you. Discover a beautiful new way to unwind in Fayetteville.'],
                'about-us' => ['About THE K Luxury Nail Bar | Fayetteville, NC', 'Discover THE K Luxury Nail Bar in Fayetteville, NC: a space for manicures, spa pedicures, lashes and brows with thoughtful, personal care.', 'Welcome to THE K LUXURY NAIL BAR, where beautiful details and a little everyday indulgence come together.'],
                'services' => ['Nail Services & Pedicures in Fayetteville, NC | THE K', 'Browse manicure, acrylic, Gel X and spa pedicure prices, plus brows, lashes, waxing and kids services at THE K Luxury Nail Bar in Fayetteville, NC.', 'Find your perfect polish, discover a new favorite ritual, or make time for the finishing details.'],
                'gallery' => ['Nail Art & Long Nail Inspiration | THE K Fayetteville', 'Explore curated long-nail inspiration and elegant nail art styles at THE K Luxury Nail Bar in Fayetteville, NC. Inspiration images are illustrative.', 'A closer look at our treatment collection, gold-inspired details and the nail styles we love.'],
                'contact-us' => ['Contact THE K Luxury Nail Bar | Fayetteville, NC', 'Call 910-491-0358 or visit THE K Luxury Nail Bar at 2804 Freedom Pkwy Dr Ste 2 Dr, Fayetteville, NC 28314. Find salon hours, directions and our contact form.', 'Find us in Fayetteville, give us a call, or leave a message. We would love to hear from you.'],
            ];
            foreach ($pages as $slug => [$title, $description, $originalDescription]) {
                $page = Page::where('slug', $slug)->first();
                if (! $page) {
                    continue;
                }
                if ($page->seo_title === $page->title.' | THE K Luxury Nail Bar · Fayetteville, NC') {
                    $page->seo_title = $title;
                }
                if ($page->seo_description === $originalDescription) {
                    $page->seo_description = $description;
                }
                if ($page->isDirty()) {
                    $page->save();
                }
            }
            $data = $profile->data;
            $data['postal_address'] ??= ['streetAddress' => '2804 Freedom Pkwy Dr Ste 2 Dr', 'addressLocality' => 'Fayetteville', 'addressRegion' => 'NC', 'postalCode' => '28314', 'addressCountry' => 'US'];
            $data['seo_revision'] = 'technical-v1';
            $profile->update(['data' => $data]);
        });
    }
}
