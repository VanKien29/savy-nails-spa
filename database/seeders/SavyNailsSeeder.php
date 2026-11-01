<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavyNailsSeeder extends Seeder
{
    public function run(): void
    {
        if (! SalonProfile::whereKey(1)->exists()) {
            $this->call(NailSalonSeeder::class);
        }

        DB::transaction(function (): void {
            $report = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true, flags: JSON_THROW_ON_ERROR));
            $logo = $report->first(fn (array $entry) => str_replace('\\', '/', $entry['file']) === 'savy/savy-logo.png');
            if (empty($logo['variants'])) {
                throw new \RuntimeException('Run npm run media:optimize before applying the Savy Nails client seed.');
            }

            SalonImage::updateOrCreate(['key' => 'savy-logo-art'], [
                'alt' => 'Savy Nails & Spa logo with white wordmark over a soft blue and pink background',
                'caption' => 'Savy Nails & Spa',
                'variants' => array_map(fn (array $variant) => array_replace($variant, ['file' => str_replace('\\', '/', $variant['file'])]), $logo['variants']),
                'category' => 'branding',
                'is_inspiration' => false,
                'show_in_gallery' => false,
                'sort_order' => 1,
                'source_credit' => 'Client-provided logo',
            ]);

            $categories = [
                ['slug' => 'manicure-nail-enhancements', 'name' => 'Manicure & Nail Enhancements', 'description' => 'Polished essentials and enhancements for every kind of finish.', 'image_id' => SalonImage::where('key', 'nails-menu')->value('id')],
                ['slug' => 'pedicure-services', 'name' => 'Pedicure Services', 'description' => 'Relaxing pedicures with warm towels, massage and thoughtful care.', 'image_id' => SalonImage::where('key', 'luxury-ritual-nude')->value('id')],
                ['slug' => 'additions', 'name' => 'Additions', 'description' => 'Small finishing details to personalize your service.', 'image_id' => SalonImage::where('key', 'luxury-look-gold')->value('id')],
                ['slug' => 'waxing', 'name' => 'Waxing', 'description' => 'Clean, polished finishing touches from brows to full face and body.', 'image_id' => SalonImage::where('key', 'brows-lashes')->value('id')],
            ];

            SalonService::query()->delete();
            SalonCategory::query()->delete();
            foreach ($categories as $index => $category) {
                SalonCategory::create($category + ['sort_order' => $index]);
            }

            $categoryIds = SalonCategory::query()->pluck('id', 'slug');
            foreach ($this->services() as $index => $service) {
                SalonService::create([
                    'category_id' => $categoryIds[$service['category']],
                    'slug' => $service['slug'],
                    'name' => $service['name'],
                    'group' => $service['group'] ?? null,
                    'description' => $service['description'] ?? null,
                    'price' => $service['price'] ?? null,
                    'price_from' => $service['price_from'] ?? false,
                    'price_note' => $service['price_note'] ?? null,
                    'sort_order' => $index,
                    'active' => true,
                ]);
            }

            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = array_replace_recursive($profile->data, [
                'city' => 'Walla Walla, Washington',
                'postal_address' => ['streetAddress' => '634 S 9th Ave', 'addressLocality' => 'Walla Walla', 'addressRegion' => 'WA', 'postalCode' => '99362', 'addressCountry' => 'US'],
                'short_city' => 'Walla Walla, WA',
                'timezone' => 'America/Los_Angeles',
                'booking_url' => null,
                'hours' => [
                    ['days' => 'Monday - Saturday', 'time' => '09:00 AM - 07:00 PM'],
                    ['days' => 'Sunday', 'time' => '10:00 AM - 05:00 PM'],
                ],
                'hours_note' => 'Please call or message us for holiday hours.',
                'gallery_note' => 'Nail inspiration is illustrative. Contact the salon for the latest service details and availability.',
                'booking_note' => 'To book, please call Savy Nails & Spa or message us through Facebook or Instagram.',
                'hero_image' => 'luxury-hero',
                'hero_mobile_image' => 'luxury-hero-mobile',
                'hero_slides' => ['luxury-hero'],
                'story_image' => 'luxury-story',
                'about_image' => 'blush-manicure',
                'about_brand_image' => 'savy-logo-art',
                'contact_image' => 'contact-welcome',
                'home_lookbook' => ['luxury-look-french', 'luxury-look-chrome', 'luxury-look-gold', 'luxury-look-milky'],
                'gallery_filters' => ['all' => 'The Collection', 'nails' => 'Nail Inspiration'],
                'copy' => [
                    'global' => [
                        'brand_monogram' => 'S', 'brand_title' => 'SAVY', 'brand_subtitle' => 'NAILS & SPA',
                        'booking_label' => 'Contact to Book', 'booking_url' => '/contact-us',
                        'policy_title' => 'Contact details',
                        'policy_body' => 'For appointments, please call the salon or message Savy Nails & Spa on Facebook or Instagram.',
                        'policy_note' => 'We do not take online bookings through this website.',
                    ],
                    'home' => [
                        'menu_label' => 'View the Menu', 'menu_url' => '/services',
                        'story_label' => 'About Savy', 'story_url' => '/about-us',
                        'full_menu_label' => 'View Full Menu', 'full_menu_url' => '/services',
                        'price_prefix' => 'From', 'ritual_label' => 'Ask About This Service', 'ritual_url' => '/contact-us',
                        'contact_label' => 'Contact the Salon', 'contact_url' => '/contact-us',
                    ],
                    'about' => [
                        'frame_title' => 'SAVY', 'frame_caption' => 'NAILS & SPA',
                        'quote' => 'Polished with care.', 'quote_accent' => 'Made for your moment.',
                        'services_label' => 'Explore the Menu', 'services_url' => '/services',
                        'values_eyebrow' => 'The Savy way',
                        'brand_heading' => 'Your moment.', 'brand_accent' => 'Your signature finish.',
                        'visit_note' => 'For appointments, call or message us directly.',
                        'contact_label' => 'Contact Savy', 'contact_url' => '/contact-us',
                    ],
                    'gallery' => [
                        'social_heading' => 'More from Savy?', 'social_body' => 'Follow along on Facebook and Instagram.', 'social_label' => 'Follow Savy',
                    ],
                    'menu' => [
                        'full_set_label' => 'Service', 'fill_label' => 'Fill', 'gel_label' => 'Gel Polish',
                        'includes_label' => 'Includes:', 'inquire_label' => '$....', 'kids_note' => 'Please contact the salon for details.',
                    ],
                    'contact' => [
                        'heading' => 'Let’s make your day', 'heading_accent' => 'a little more beautiful.',
                        'visit_label' => 'Visit the Salon', 'directions_label' => 'Open in Google Maps',
                        'phone_label' => 'Give Us a Call', 'hours_label' => 'Business Hours', 'social_label' => 'Connect With Us',
                        'map_heading' => 'Find Savy', 'map_accent' => 'in Walla Walla.', 'map_label' => 'Open in Google Maps',
                        'menu_empty' => 'Please call or message the salon for menu details.',
                    ],
                    'footer' => [
                        'intro' => 'A polished moment, a little self-care, and your own kind of beautiful.',
                        'hours_label' => 'Business Hours', 'call_label' => 'Give us a call', 'signature' => 'Nails · Spa · Waxing',
                    ],
                ],
                'savy_revision' => '2026-11-v1',
            ]);
            $profile->update(['data' => $data]);

            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), [
                'site_name' => 'Savy Nails & Spa', 'company_name' => 'Savy Nails & Spa', 'slogan' => 'Polished with care. Made for your moment.',
                'primary_color' => '#c6a15b', 'secondary_color' => '#2b241c', 'accent_text_color' => '#8a642b',
                'background_color' => '#fffdf9', 'surface_color' => '#f7f0e4', 'home_gold_color' => '#c6a15b', 'home_dark_color' => '#2b241c',
                'heading_font' => 'serif', 'body_font' => 'sans', 'hotline' => '509-529-7289',
                'address' => '634 S 9th Ave, Walla Walla, WA 99362', 'maps_url' => SavyMapLinkSeeder::MAP_URL, 'maps_embed_url' => '',
                'copyright' => 'All rights reserved.', 'legal' => 'Nails · Spa · Waxing',
                'default_title' => 'Savy Nails & Spa | Nails, Spa & Waxing in Walla Walla, WA',
                'default_description' => 'Visit Savy Nails & Spa in Walla Walla, WA for manicures, nail enhancements, spa pedicures and waxing. Call or message us to book.',
                'social_links' => [
                    ['label' => 'Google', 'url' => SavyMapLinkSeeder::MAP_URL],
                    ['label' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61557425844879'],
                    ['label' => 'Instagram', 'url' => 'https://www.instagram.com/savynailsspa?utm_source=ig_web_button_share_sheet&rpxt=ZDNlZDc0MzIxNw=='],
                ],
                'contacts' => [['type' => 'phone', 'url' => 'tel:+15095297289', 'color' => '#8a642b', 'desktop' => true, 'mobile' => true, 'enabled' => true]],
                'features' => ['contact' => false, 'multilingual' => false, 'animations' => true],
                'logo_id' => null, 'mobile_logo_id' => null,
            ]));

            foreach (Page::whereIn('slug', ['home', 'about-us', 'services', 'gallery', 'contact-us'])->get() as $page) {
                $page->update($this->pageContent($page->slug));
            }
            Page::where('slug', 'booking')->update(['status' => 'draft', 'exportable' => false]);

            NavigationItem::whereIn('location', ['header', 'footer'])->update(['enabled' => false]);
            foreach ([['Home', '/'], ['About Us', '/about-us'], ['Services', '/services'], ['Gallery', '/gallery'], ['Contact Us', '/contact-us']] as $index => [$label, $url]) {
                NavigationItem::updateOrCreate(['url' => $url, 'location' => 'header'], ['label' => $label, 'sort_order' => $index, 'enabled' => true]);
                NavigationItem::updateOrCreate(['url' => $url, 'location' => 'footer'], ['label' => $label, 'sort_order' => $index, 'enabled' => true]);
            }
        });

        app(SalonContent::class)->forget();
    }

    private function pageContent(string $slug): array
    {
        $copy = [
            'home' => ['title' => 'Home', 'seo_title' => 'Savy Nails & Spa | Nails, Spa & Waxing in Walla Walla, WA', 'seo_description' => 'Visit Savy Nails & Spa in Walla Walla, WA for polished manicures, spa pedicures, nail enhancements and waxing.'],
            'about-us' => ['title' => 'About Savy', 'seo_title' => 'About Savy Nails & Spa | Walla Walla, WA', 'seo_description' => 'Meet Savy Nails & Spa in Walla Walla, WA - a polished place for beautiful nails, relaxing spa services and personal care.'],
            'services' => ['title' => 'Services', 'seo_title' => 'Nail Services & Pedicures | Savy Nails & Spa Walla Walla', 'seo_description' => 'Explore manicures, nail enhancements, pedicures, additions and waxing at Savy Nails & Spa in Walla Walla, WA.'],
            'gallery' => ['title' => 'Gallery', 'seo_title' => 'Nail Inspiration | Savy Nails & Spa Walla Walla', 'seo_description' => 'Explore a little nail inspiration from Savy Nails & Spa in Walla Walla, WA.'],
            'contact-us' => ['title' => 'Contact', 'seo_title' => 'Contact Savy Nails & Spa | Walla Walla, WA', 'seo_description' => 'Find Savy Nails & Spa at 634 S 9th Ave, Walla Walla, WA 99362. Call 509-529-7289 or connect with us on social.'],
        ][$slug];

        if ($slug === 'home') {
            $sections = [
                ['id' => 'hero', 'type' => 'hero', 'variant' => 'default', 'enabled' => true, 'heading' => 'Nail care,', 'body' => 'A polished space for manicures, spa pedicures, enhancements and waxing in Walla Walla.', 'items' => [['title' => 'with a golden touch.']]],
                ['id' => 'intro', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'A little time for you.', 'body' => 'Step into Savy Nails & Spa for thoughtful care, beautiful finishes and a moment to slow down.'],
                ['id' => 'collection', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'Your next signature look.', 'body' => 'Explore our menu, then call or message us to find a time that works for you.'],
                ['id' => 'lookbook', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'A little inspiration.', 'body' => 'Bring your favorite finish, shape or color idea and let’s make it yours.'],
                ['id' => 'finale', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'Ready for your next set?', 'body' => 'Call the salon or connect with Savy on Facebook and Instagram to book.'],
            ];
        } elseif ($slug === 'about-us') {
            $sections = [
                ['id' => 'hero', 'type' => 'hero', 'variant' => 'default', 'enabled' => true, 'heading' => 'Beauty in the details.', 'body' => 'A calm, polished place for your next nail or spa moment.', 'items' => [['title' => 'Made for your moment.']]],
                ['id' => 'intro', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'A personal touch.', 'body' => 'We believe self-care can be simple: thoughtful service, beautiful results and time set aside just for you.'],
                ['id' => 'story', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'More than a manicure.', 'body' => 'From a fresh polish to a full spa pedicure, Savy Nails & Spa is here for the little details that make you feel put together.'],
                ['id' => 'promise', 'type' => 'about', 'variant' => 'default', 'enabled' => true, 'heading' => 'The Savy way.', 'body' => 'Come as you are, bring your inspiration and leave with a finish that feels like you.', 'items' => [['title' => 'Carefully finished', 'text' => 'Beautiful details, considered from start to finish.'], ['title' => 'Relaxed and welcoming', 'text' => 'A space to settle in, slow down and enjoy your service.'], ['title' => 'Your style, your way', 'text' => 'Classic, clean or expressive - we are here to help you choose.']]],
            ];
        } else {
            $sections = [
                ['id' => 'hero', 'type' => 'hero', 'variant' => 'default', 'enabled' => true, 'heading' => $slug === 'contact-us' ? 'Come say hello.' : ucfirst($slug).'.', 'body' => $slug === 'contact-us' ? 'For appointments, please call or message Savy Nails & Spa directly.' : 'Explore the Savy Nails & Spa experience.', 'items' => [['title' => $slug === 'contact-us' ? 'We would love to hear from you.' : 'A beautiful finish starts here.']]],
            ];
        }

        return array_replace($copy, [
            'template' => $slug === 'home' ? 'salon-savy-home' : match ($slug) {
                'about-us' => 'salon-about',
                'services' => 'salon-services',
                'gallery' => 'salon-gallery',
                'contact-us' => 'salon-contact',
                default => 'salon-content',
            },
            'sections' => $sections,
            'status' => 'published',
            'exportable' => true,
        ]);
    }

    private function services(): array
    {
        $row = static fn (string $category, string $group, string $name, ?float $price = null, bool $from = false, ?string $note = null, ?string $description = null): array => [
            'category' => $category, 'group' => $group, 'name' => $name, 'slug' => Str::slug($category.'-'.$group.'-'.$name), 'price' => $price, 'price_from' => $from, 'price_note' => $note, 'description' => $description,
        ];

        return [
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Regular Manicure', 30),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Gel Manicure', 40),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Full Set', 60, true),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Dip Powder', 55, true),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Fill with Gel', 55, true),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Builder Gel', 65),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Gel Overlay', null, false, '$....'),
            $row('manicure-nail-enhancements', 'Manicure & Nail Enhancements', 'Solar Nails', null, false, '$....'),
            $row('pedicure-services', 'Pedicure Services', 'Regular Pedicure', 40, false, null, 'Nail trimming, cuticle care, lotion massage, hot towel & polish'),
            $row('pedicure-services', 'Pedicure Services', 'Deluxe Pedicure', 50, false, null, 'Nail trimming, cuticle care, callus removal, sea-salt massage, hot paraffin, lotion massage, hot towel & polish'),
            $row('pedicure-services', 'Pedicure Services', 'Organic & Deluxe Pedicure', 55, false, null, 'Nail trimming, cuticle care, callus removal, sea-salt massage, mask, lotion massage, hot towel & polish'),
            $row('pedicure-services', 'Pedicure Services', 'Ultimate Pedicure', 60, false, null, 'Nail trimming, cuticle care, lotion massage, callus removal, sea-salt massage, hot paraffin, hot stone, mask, hot towel & polish'),
            $row('pedicure-services', 'Pedicure Services', 'Organic & Detox Ultimate Pedicure', 65, false, null, 'Nail trimming, cuticle care, callus removal, sea-salt massage, hot paraffin, hot stone, mask, lotion massage, hot towel & polish'),
            $row('pedicure-services', 'Pedicure Services', 'Volcano Spa Pedicure', 75, false, null, 'Nail trimming, cuticle care, callus removal, sea-salt massage, hot paraffin, hot stone, mask, lotion massage, hot towel & polish'),
            $row('additions', 'Additions', 'Gel Color', 15, true),
            $row('additions', 'Additions', 'Gel White Tip', 20, true),
            $row('additions', 'Additions', 'Nail Design', null, false, '$....'),
            $row('additions', 'Additions', 'Color Change', 20),
            $row('additions', 'Additions', 'Color Gel', 30),
            $row('waxing', 'Waxing', 'Eyebrows', 20),
            $row('waxing', 'Waxing', 'Lips', 12),
            $row('waxing', 'Waxing', 'Bikini', 40, true),
            $row('waxing', 'Waxing', 'Half Arm', 25, true),
            $row('waxing', 'Waxing', 'Full Arm', 45, true),
            $row('waxing', 'Waxing', 'Half Leg', 40, true),
            $row('waxing', 'Waxing', 'Full Leg', 60, true),
            $row('waxing', 'Waxing', 'Feet', 20),
            $row('waxing', 'Waxing', 'Full Face Wax', 50, true),
            $row('waxing', 'Waxing', 'Cheeks', null, false, '$....'),
            $row('waxing', 'Waxing', 'Hands', null, false, '$....'),
            $row('waxing', 'Waxing', 'Neck', null, false, '$....'),
        ];
    }
}
