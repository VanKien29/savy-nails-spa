<?php

namespace App\Services;

use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use Illuminate\Support\Facades\Cache;

class SalonContent
{
    private ?array $loaded = null;

    public function all(): array
    {
        return $this->loaded ??= Cache::remember('salon.catalog', 600, fn () => [
            'profile' => SalonProfile::find(1)?->data ?? [],
            'categories' => SalonCategory::with(['image', 'services.image'])->orderBy('sort_order')->get(),
            'images' => SalonImage::orderBy('sort_order')->get()->keyBy('key'),
        ]);
    }

    public function structuredData(): array
    {
        $profile = $this->all()['profile'];
        $address = $profile['postal_address'] ?? [];
        if (count(array_filter($address)) < 5) {
            return [];
        }
        $settings = app(SiteSettingsService::class);
        $data = ['@type' => 'NailSalon', 'address' => ['@type' => 'PostalAddress'] + $address];
        $phone = preg_replace('/[^0-9+]/', '', $settings->get('hotline', ''));
        if ($phone) {
            $data['telephone'] = $address['addressCountry'] === 'US' && strlen($phone) === 10 ? '+1'.$phone : $phone;
        }
        if ($settings->get('maps_url')) {
            $data['hasMap'] = $settings->get('maps_url');
        }
        $week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($profile['hours'] ?? [] as $hour) {
            $days = preg_split('/\s*[–—-]\s*/u', $hour['days']);
            $first = array_search($days[0], $week, true);
            $last = array_search(end($days), $week, true);
            $times = preg_split('/\s*[–—-]\s*/u', $hour['time']);
            if ($first === false || $last === false || $last < $first || count($times) !== 2) {
                continue;
            }
            $open = \DateTimeImmutable::createFromFormat('!h:i A', $times[0]);
            $close = \DateTimeImmutable::createFromFormat('!h:i A', $times[1]);
            if (! $open || ! $close || $open->format('h:i A') !== $times[0] || $close->format('h:i A') !== $times[1]) {
                continue;
            }
            $data['openingHoursSpecification'][] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => array_map(fn ($day) => 'https://schema.org/'.$day, array_slice($week, $first, $last - $first + 1)), 'opens' => $open->format('H:i'), 'closes' => $close->format('H:i')];
        }

        return $data;
    }
}
