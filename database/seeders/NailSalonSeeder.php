<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NailSalonSeeder extends Seeder
{
    public function run(): void
    {
        $source = [
            'categories' => SalonCatalogData::categories(),
            'services' => SalonCatalogData::services(),
            'images' => SalonCatalogData::menuImages(),
        ];
        $reportPath = public_path('assets/optimized/report.json');
        if (! is_file($reportPath)) {
            throw new \RuntimeException('Run npm run media:optimize before installing the salon.');
        }
        $report = collect(json_decode(file_get_contents($reportPath), true, flags: JSON_THROW_ON_ERROR));
        // The public booking page was retired; keep the legacy record only for existing lead references.
        Page::where('slug', 'booking')->update(['exportable' => false]);
        DB::transaction(function () use ($source, $report) {
            $new = ! SalonProfile::find(1);
            SalonProfile::firstOrCreate(['id' => 1], ['data' => [
                'city' => 'Fayetteville, North Carolina', 'short_city' => 'Fayetteville, NC', 'timezone' => 'America/New_York',
                'booking_url' => null, 'hours' => [['days' => 'Monday – Saturday', 'time' => '09:30 AM – 07:30 PM'], ['days' => 'Sunday', 'time' => '09:30 AM – 07:00 PM']], 'hours_note' => 'Please call for holiday hours.',
                'gallery_note' => 'Explore our treatment collection and curated nail inspiration. Manicure inspiration images are illustrative; they are not photographs of client work.',
                'booking_note' => 'Your preferred date and time are a request. Our team will contact you to confirm availability.',
                'gallery_filters' => ['all' => 'All', 'nails' => 'Nail Inspiration', 'rituals' => 'Spa Rituals', 'beauty' => 'Brows & Lashes'],
            ]]);
            foreach ($source['images'] as $index => $row) {
                $entry = $report->first(fn ($entry) => str_replace('\\', '/', $entry['file']) === 'the-k/'.$row['source']);
                if (empty($entry['variants'])) {
                    throw new \RuntimeException('Missing optimized source: '.$row['source']);
                }
                $variants = array_map(fn ($variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $entry['variants']);
                SalonImage::firstOrCreate(['key' => $row['key']], array_replace(collect($row)->except(['key', 'source'])->all(), ['variants' => $variants, 'sort_order' => $index]));
            }
            $images = SalonImage::all()->keyBy('key');
            foreach ($source['categories'] as $index => $row) {
                SalonCategory::firstOrCreate(['slug' => $row['slug']], ['name' => $row['name'], 'description' => $row['description'], 'image_id' => $images[$row['image']]->id, 'sort_order' => $index]);
            }
            $categories = SalonCategory::all()->keyBy('slug');
            foreach ($source['services'] as $index => $row) {
                $slug = $row['slug'] ?? Str::slug($row['category'].'-'.($row['group'] ?? '').'-'.$row['name']);
                SalonService::firstOrCreate(['slug' => $slug], array_replace(collect($row)->except(['category', 'image'])->all(), ['category_id' => $categories[$row['category']]->id, 'image_id' => isset($row['image']) ? $images[$row['image']]->id : null, 'sort_order' => $index, 'active' => true]));
            }
            if (! $new) {
                return;
            } // Re-running preserves edited content and prices.
            foreach (Page::where('is_home', true)->get() as $old) {
                $old->update(['is_home' => false, 'status' => 'draft']);
            }
            foreach (Page::whereIn('template', ['sections', 'studio'])->get() as $old) {
                $old->update(['status' => 'draft']);
            }
            foreach (NavigationItem::all() as $item) {
                $item->update(['enabled' => false]);
            }
            $settings = array_replace(SiteSettingsService::defaults(), [
                'site_name' => 'THE K LUXURY NAIL BAR', 'company_name' => 'THE K LUXURY NAIL BAR',
                'slogan' => 'A little moment. A beautiful difference.', 'primary_color' => '#ae8346', 'secondary_color' => '#30271f',
                'heading_font' => 'serif', 'body_font' => 'sans', 'hotline' => '910-491-0358', 'email' => '',
                'address' => '2804 Freedom Pkwy Dr Ste 2 Dr, Fayetteville, NC 28314', 'maps_url' => 'https://maps.app.goo.gl/rZcnSx2muwHrotw88',
                'copyright' => 'All rights reserved.', 'legal' => 'Nails · Lashes · Brows',
                'default_title' => 'THE K Luxury Nail Bar | Nails, Lashes & Brows in Fayetteville, NC',
                'default_description' => 'Discover nails, spa pedicures, brows and lashes at THE K LUXURY NAIL BAR in Fayetteville, NC. Explore our menu and request your next visit.',
                'social_links' => [
                    ['label' => 'Google', 'url' => 'https://maps.app.goo.gl/rZcnSx2muwHrotw88'],
                    ['label' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61594988307910&locale=vi_VN'],
                    ['label' => 'Instagram', 'url' => 'https://www.instagram.com/thek.luxurynailbar?utm_source=ig_web_button_share_sheet&srtk=ZDNlZDc0MzIxNw=='],
                ],
                'contacts' => [['type' => 'phone', 'url' => 'tel:+19104910358', 'color' => '#8c6532', 'desktop' => true, 'mobile' => true, 'enabled' => true]],
                'features' => ['contact' => true, 'multilingual' => false, 'animations' => true],
            ]);
            // SEO uses the existing upload/variant engine; public editorial images use the build pipeline.
            $hero = app(MediaService::class)->upload(new UploadedFile(resource_path('media/the-k/hero-manicure.png'), 'hero-manicure.png', 'image/png', null, true), 'French manicure with delicate gold accents');
            $settings['og_media_id'] = $hero->id;
            app(SiteSettingsService::class)->update($settings);
            $section = fn ($id, $heading, $body = '', $items = []) => ['type' => $id === 'hero' ? 'hero' : 'about', 'variant' => 'default', 'enabled' => true, 'id' => $id, 'heading' => $heading, 'body' => $body, 'items' => $items];
            $pages = [
                ['home', 'Home', 'Your little moment of luxury.', 'Nails, lashes, brows — and a little time that belongs entirely to you. Discover a beautiful new way to unwind in Fayetteville.', 'A moment for you. A finish to remember.'],
                ['about-us', 'About Us', 'An elevated escape. A personal touch.', 'Welcome to THE K LUXURY NAIL BAR, where beautiful details and a little everyday indulgence come together.', 'Thoughtfully considered. Beautifully you.'],
                ['services', 'Services', 'A beautiful kind of self-care.', 'Find your perfect polish, discover a new favorite ritual, or make time for the finishing details.', 'Explore our treatment menu'],
                ['gallery', 'Gallery', 'A little inspiration. A lot to love.', 'A closer look at our treatment collection, gold-inspired details and the nail styles we love.', 'The inspiration edit'],
                ['contact-us', 'Contact Us', 'A beautiful visit starts here.', 'Find us in Fayetteville, give us a call, or leave a message. We would love to hear from you.', 'Come say hello'],
                ['booking', 'Book Now', 'Make a little time for you.', 'Choose your treatment and tell us when you would love to visit. Our team will be in touch to confirm.', 'Request your appointment'],
            ];
            foreach ($pages as [$slug,$title,$heading,$body,$intro]) {
                Page::firstOrCreate(['slug' => $slug], [
                    'title' => $title, 'template' => 'salon-'.($slug === 'about-us' ? 'about' : ($slug === 'contact-us' ? 'contact' : $slug)),
                    'status' => 'published', 'is_home' => $slug === 'home', 'exportable' => $slug !== 'booking',
                    'seo_title' => $title.' | THE K Luxury Nail Bar · Fayetteville, NC', 'seo_description' => $body,
                    'og_media_id' => $hero->id, 'sections' => [
                        $section('hero', $heading, $body),
                        $section('intro', $intro, 'A fresh set, a soothing ritual, a detail that makes all the difference. Find your own kind of luxury at THE K.'),
                        $section('story', 'More than a manicure. A moment to yourself.', 'We believe the best beauty rituals feel personal. From a classic manicure to a rose-inspired pedicure, a polished brow to a fresh set of lashes, our menu brings the details together in one beautifully considered place.'),
                        $section('promise', 'The beauty is in the details.', 'Choose a familiar favorite or try something a little different. Our team is here to help you find a finish that feels like you.', [
                            ['title' => 'Your style, your way', 'text' => 'Natural and understated or a little more expressive — choose the look that feels right for you.'],
                            ['title' => 'A ritual worth slowing down for', 'text' => 'From lavender to rose and botanical tea, explore a spa menu full of thoughtful touches.'],
                            ['title' => 'The finishing details', 'text' => 'Nails, lashes and brows, brought together for your everyday beauty ritual.'],
                        ]),
                    ],
                ]);
            }
            foreach ([['Home', '/'], ['About Us', '/about-us'], ['Services', '/services'], ['Gallery', '/gallery'], ['Contact Us', '/contact-us']] as $index => [$label,$url]) {
                NavigationItem::updateOrCreate(['url' => $url, 'location' => 'header'], ['label' => $label, 'sort_order' => $index, 'enabled' => true]);
                NavigationItem::updateOrCreate(['url' => $url, 'location' => 'footer'], ['label' => $label, 'sort_order' => $index, 'enabled' => true]);
            }
            Page::where('slug', 'booking')->update(['exportable' => false]);
        });
        $this->call(SalonInspirationSeeder::class);
        $this->call(SalonLuxurySeeder::class);
        $this->call(SalonLayoutTrimSeeder::class);
        $this->call(SalonRitualRefreshSeeder::class);
        $this->call(SalonSeoSeeder::class);
        $this->call(SalonBrandSeeder::class);
        $this->call(SalonHeroSlideshowSeeder::class);
        $this->call(SalonClientMediaSeeder::class);
        $this->call(SalonMapSeeder::class);
        $this->call(SalonClientDesignSeeder::class);
        $this->call(SalonClientMenuSeeder::class);
    }
}
