<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

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
        static::saved(fn () => Cache::forget('salon.catalog'));
        static::saving(fn (self $profile) => Validator::make($profile->data ?? [], [
            'booking_url' => 'nullable|url:https', 'hours' => 'nullable|array', 'hours.*.days' => 'required|string|max:80', 'hours.*.time' => 'required|string|max:100',
            'hero_slides' => 'nullable|array|max:6', 'hero_slides.*' => ['required', 'string', 'regex:/^[a-z0-9-]+$/'],
            'postal_address' => 'nullable|array:streetAddress,addressLocality,addressRegion,postalCode,addressCountry',
            'postal_address.streetAddress' => 'required_with:postal_address|string|max:200', 'postal_address.addressLocality' => 'required_with:postal_address|string|max:100',
            'postal_address.addressRegion' => 'required_with:postal_address|string|max:100', 'postal_address.postalCode' => 'required_with:postal_address|string|max:20',
            'postal_address.addressCountry' => ['required_with:postal_address', 'regex:/^[A-Z]{2}$/'],
        ])->validate());
    }
}
