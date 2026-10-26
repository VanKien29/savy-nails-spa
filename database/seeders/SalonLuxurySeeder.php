<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalonLuxurySeeder extends Seeder
{
    public function run(): void
    {
        $profile = SalonProfile::findOrFail(1);
        // Existing CMS sites receive the narrow client-design upgrade without resetting owner content.
        if (in_array($profile->data['luxury_revision'] ?? null, ['editorial-v5', 'editorial-v6'], true)) {
            return;
        }
        $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
        $manifest = SalonCatalogData::luxuryImages();
        DB::transaction(function () use ($profile, $report, $manifest) {
            foreach (SalonImage::where('show_in_gallery', true)->get() as $old) {
                $old->update(['show_in_gallery' => false]);
            }
            foreach ($manifest as $index => $row) {
                $entry = $report->first(fn ($item) => str_replace('\\', '/', $item['file']) === 'the-k/'.$row['source']);
                if (empty($entry['variants'])) {
                    throw new \RuntimeException('Run media:optimize for '.$row['source']);
                }
                $variants = array_map(fn ($item) => array_replace($item, ['file' => str_replace('\\', '/', $item['file'])]), $entry['variants']);
                SalonImage::updateOrCreate(['key' => $row['key']], ['alt' => $row['alt'], 'caption' => $row['caption'], 'variants' => $variants, 'category' => 'nails', 'is_inspiration' => true, 'show_in_gallery' => str_starts_with($row['placement'], 'gallery.'), 'sort_order' => $index, 'source_url' => null, 'source_credit' => 'AI-created editorial inspiration']);
            }
            $data = $profile->data;
            $data['luxury_revision'] = 'editorial-v6';
            $data['hero_image'] = 'luxury-hero';
            $data['hero_mobile_image'] = 'luxury-hero-mobile';
            $data['story_image'] = 'luxury-story';
            $data['ritual_image'] = 'luxury-ritual';
            $data['about_image'] = 'blush-manicure';
            $data['booking_image'] = 'hero-manicure';
            $data['home_lookbook'] = ['luxury-look-french', 'luxury-look-chrome', 'luxury-look-gold', 'luxury-look-milky'];
            $data['gallery_filters'] = ['all' => 'The Collection', 'nails' => 'Nail Inspiration'];
            $data['gallery_note'] = 'An editorial collection of AI-created nail inspiration. These images explore styles and finishes; they are not photographs of salon client work.';
            $profile->update(['data' => $data]);
            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), ['primary_color' => '#b49563', 'secondary_color' => '#201f1c']));
            $home = Page::where('template', 'salon-home')->firstOrFail();
            $section = fn ($id, $eyebrow, $heading, $body = '', $items = []) => ['id' => $id, 'type' => $id === 'hero' ? 'hero' : 'about', 'variant' => 'default', 'enabled' => true, 'eyebrow' => $eyebrow, 'heading' => $heading, 'body' => $body, 'items' => $items];
            $home->update(['sections' => [
                $section('hero', 'THE K LUXURY NAIL BAR', 'Your moment.', 'Beautiful nails. Unhurried rituals. Discover your own kind of luxury in Fayetteville.', [['title' => 'Beautifully elevated.']]),
                $section('intro', 'Welcome to THE K', 'An everyday ritual. An extraordinary feeling.', 'Make room for a little beauty in your day. From a fresh set to a beautifully considered pedicure, find a treatment that feels like you.'),
                $section('collection', 'The treatment collection', 'Find your kind of beautiful.', 'Nails, spa pedicures, brows, lashes and the finishing details — all in one beautifully considered place.'),
                $section('ritual', 'The signature experience', 'A slower kind of luxury.', 'Trade the rush for a ritual. Discover a botanical spa experience with warm towels, soothing massage and thoughtful finishing touches.', [
                    ['title' => 'A slower kind of luxury', 'text' => 'Take a breath and settle into a botanical spa ritual with warm towels, soothing massage and a finish that feels beautifully unhurried.', 'label' => 'luxury-ritual'],
                    ['title' => 'Thoughtful from start to finish', 'text' => 'Every detail is considered, from the first touch to the final polish, so your visit feels personal, calm and entirely yours.', 'label' => 'luxury-gallery-sculpture'],
                ]),
                $section('lookbook', 'The signature edit', 'Understated. Unforgettable.', 'Soft neutrals. Sculpted lengths. Just a little gold. Explore four expressions of everyday elegance.'),
                $section('journey', 'Your visit, considered', 'A beautiful experience starts simply.', 'A little guidance for your next moment at THE K.', [
                    ['title' => 'Find your ritual', 'text' => 'Explore our service menu and choose a familiar favorite or something new.'],
                    ['title' => 'Make a little time', 'text' => 'Send your preferred date and time. Our team will contact you to confirm your visit.'],
                    ['title' => 'Make it yours', 'text' => 'Bring your inspiration and tell us about the finish you have in mind.'],
                ]),
                $section('finale', 'A little time, just for you', 'Good nails. Even better moments.', 'Your next beautiful detail starts here.'),
            ]]);
            Cache::forget('salon.catalog');
        });
    }
}
