<?php

namespace App\Models;

use App\Services\SalonContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SalonProfile extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(SalonContent::class)->forget();
        });
        static::saving(function (self $profile) {
            $copyRules = [];
            foreach (config('cms.copy') as $group => $fields) {
                foreach ($fields as $key => $field) {
                    if (str_ends_with($key, '_url')) {
                        $copyRules['copy.'.$group.'.'.$key] = ['sometimes', 'required', 'regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+)$~'];
                    }
                }
            }
            Validator::make($profile->data ?? [], [
                'booking_url' => 'nullable|url:https', 'hours' => 'nullable|array', 'hours.*.days' => 'required|string|max:80', 'hours.*.time' => 'required|string|max:100',
                'city' => 'nullable|string|max:150', 'short_city' => 'nullable|string|max:100', 'timezone' => 'nullable|timezone',
                'hero_image' => 'nullable|exists:salon_images,key', 'story_image' => 'nullable|exists:salon_images,key', 'about_image' => 'nullable|exists:salon_images,key', 'contact_image' => 'nullable|exists:salon_images,key',
                'menu_brow_images' => 'nullable|array|max:3', 'menu_brow_images.*' => 'required|distinct|exists:salon_images,key',
                'home_lookbook' => 'nullable|array|max:24', 'home_lookbook.*' => 'required|exists:salon_images,key',
                'gallery_filters' => 'nullable|array|max:12', 'gallery_filters.*' => 'required|string|max:100',
                'copy' => 'nullable|array', 'copy.*' => 'array', 'copy.*.*' => 'required|string|max:2000',
                'hero_slides' => 'nullable|array|max:6', 'hero_slides.*' => ['required', 'string', 'regex:/^[a-z0-9-]+$/', 'exists:salon_images,key'],
                'postal_address' => 'nullable|array:streetAddress,addressLocality,addressRegion,postalCode,addressCountry',
                'postal_address.streetAddress' => 'required_with:postal_address|string|max:200', 'postal_address.addressLocality' => 'required_with:postal_address|string|max:100',
                'postal_address.addressRegion' => 'required_with:postal_address|string|max:100', 'postal_address.postalCode' => 'required_with:postal_address|string|max:20',
                'postal_address.addressCountry' => ['required_with:postal_address', 'regex:/^[A-Z]{2}$/'],
            ] + $copyRules)->validate();
            foreach (array_keys($profile->data['gallery_filters'] ?? []) as $key) {
                if (! preg_match('/^[a-z0-9-]+$/', $key)) {
                    throw ValidationException::withMessages(['data.gallery_filters' => 'Mã nhóm chỉ gồm chữ thường, số và dấu gạch ngang.']);
                }
            }
        });
    }
}
