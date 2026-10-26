<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SiteSettingsService
{
    public const SOCIAL_CHANNELS = [
        'Google' => 'Google Maps', 'Facebook' => 'Facebook', 'Instagram' => 'Instagram',
        'TikTok' => 'TikTok', 'YouTube' => 'YouTube', 'Zalo' => 'Zalo',
        'WhatsApp' => 'WhatsApp', 'Messenger' => 'Messenger', 'Telegram' => 'Telegram',
        'X' => 'X (Twitter)', 'Threads' => 'Threads', 'Pinterest' => 'Pinterest',
        'LinkedIn' => 'LinkedIn', 'Discord' => 'Discord', 'Snapchat' => 'Snapchat',
        'Reddit' => 'Reddit', 'WeChat' => 'WeChat', 'LINE' => 'LINE',
        'Viber' => 'Viber', 'Twitch' => 'Twitch',
    ];

    private ?array $loaded = null;

    public static function defaults(): array
    {
        return [
            'site_name' => 'Forma Studio', 'company_name' => 'Forma Studio', 'slogan' => 'Thiết kế có chiều sâu. Tăng trưởng có định hướng.',
            'primary_color' => '#d9f36a', 'secondary_color' => '#153c35', 'heading_font' => 'sans', 'body_font' => 'sans',
            'accent_text_color' => '#88622f', 'background_color' => '#ffffff', 'surface_color' => '#f3ede3', 'home_gold_color' => '#cfb17f', 'home_dark_color' => '#151513',
            'email' => '', 'hotline' => '', 'address' => '', 'maps_url' => '', 'maps_embed_url' => '', 'copyright' => 'Mọi quyền được bảo lưu.', 'legal' => '',
            'logo_id' => null, 'mobile_logo_id' => null, 'favicon_id' => null, 'og_media_id' => null,
            'default_title' => 'Forma — Đồng hành cùng thương hiệu của bạn', 'default_description' => 'Giải pháp chiến lược, thiết kế và trải nghiệm số cho doanh nghiệp đang hướng tới bước phát triển tiếp theo.',
            'ga4_id' => '', 'gtm_id' => '', 'meta_pixel_id' => '', 'social_links' => [],
            'script_head' => '', 'script_body_end' => '',
            'contacts' => [], 'contact_position' => 'right', 'back_to_top' => true, 'cookie_consent' => true,
            'features' => ['contact' => true, 'multilingual' => false, 'animations' => true],
        ];
    }

    public function all(): array
    {
        return $this->loaded ??= Cache::remember('starter.settings', 600, function () {
            $stored = SiteSetting::find(1)?->data ?? [];

            return array_replace(self::defaults(), $stored, ['features' => array_replace(self::defaults()['features'], $stored['features'] ?? [])]);
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    public function update(array $data): void
    {
        $rules = [
            'site_name' => 'required|string|max:100', 'company_name' => 'required|string|max:160', 'slogan' => 'nullable|string|max:240',
            'primary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'], 'secondary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'heading_font' => ['required', Rule::in(['sans', 'serif', 'system'])], 'body_font' => ['required', Rule::in(['sans', 'serif', 'system'])],
            'email' => 'nullable|email|max:160', 'hotline' => ['nullable', 'regex:/^\+?[0-9 ()-]{6,24}$/'],
            'address' => 'nullable|string|max:500', 'maps_url' => ['nullable', 'url:https', 'regex:~^https://(?:www\.)?(?:google\.com/maps|maps\.google\.com|maps\.app\.goo\.gl)/~'],
            'maps_embed_url' => ['nullable', 'url:https', 'max:4000', 'regex:~^https://www\.google\.com/maps/embed\?pb=[^\s<>]+$~'],
            'copyright' => 'nullable|string|max:200', 'legal' => 'nullable|string|max:2000',
            'default_title' => 'required|string|max:160', 'default_description' => 'required|string|max:320',
            'ga4_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'], 'gtm_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]+$/'], 'meta_pixel_id' => ['nullable', 'regex:/^[0-9]{5,30}$/'],
            'script_head' => 'nullable|string', 'script_body_end' => 'nullable|string',
            'social_links' => 'array|max:20', 'social_links.*.label' => ['required', Rule::in(array_keys(self::SOCIAL_CHANNELS))], 'social_links.*.url' => 'required|url:https',
            'contact_position' => ['required', Rule::in(['left', 'right'])], 'back_to_top' => 'required|boolean', 'cookie_consent' => 'required|boolean',
            'contacts' => 'array|max:8', 'contacts.*' => 'array:type,url,color,desktop,mobile,enabled',
            'contacts.*.type' => ['required', Rule::in(['phone', 'zalo', 'messenger', 'whatsapp'])],
            'contacts.*.url' => ['required', 'max:240', 'regex:~^(?:https://[^\s]+|tel:\+?[0-9 -]+)$~'],
            'contacts.*.color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'contacts.*.desktop' => 'required|boolean', 'contacts.*.mobile' => 'required|boolean', 'contacts.*.enabled' => 'required|boolean',
            'features' => 'array:contact,multilingual,animations', 'features.*' => 'boolean',
        ];
        foreach (['logo_id', 'mobile_logo_id', 'favicon_id', 'og_media_id'] as $key) {
            $rules[$key] = 'nullable|exists:media,id';
        }
        foreach (['accent_text_color', 'background_color', 'surface_color', 'home_gold_color', 'home_dark_color'] as $key) {
            $rules[$key] = ['required', 'regex:/^#[a-fA-F0-9]{6}$/'];
        }
        $validated = Validator::make($data, $rules)->validate();
        SiteSetting::updateOrCreate(['id' => 1], ['data' => $validated]);
        $this->forget();
    }

    public function forget(): void
    {
        $this->loaded = null;
        Cache::forget('starter.settings');
    }
}
